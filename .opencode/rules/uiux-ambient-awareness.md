---
description: Battery-aware UI, light sensor adaptation, network-adaptive loading, and context-aware interfaces. Making apps that respect their environment.
---

# Ambient Awareness

> Great apps adapt to their environment. This file covers battery-aware UI,
> light sensor theming, network-adaptive loading, and respecting device context.
> The difference between "feels native" and "feels like a website."

---

## CRITICAL RULES

1. **Progressive enhancement** — These are optimizations, not requirements.
2. **Respect privacy** — Never use sensors for tracking. Local only.
3. **Graceful fallback** — Always have a default if API unavailable.
4. **User control** — Let users override automatic behavior.
5. **Performance** — Sensor polling must be throttled.

---

## BATTERY-AWARE UI

Reduce animations and features when battery is low:

```ts
// batteryAwareness.ts
interface BatteryState {
  level: number;         // 0 to 1
  charging: boolean;
  chargingTime: number;  // Seconds until full
  dischargingTime: number;
}

class BatteryManager {
  private battery: BatteryState | null = null;
  private listeners: Set<(state: BatteryState) => void> = new Set();
  private lowBatteryThreshold = 0.2; // 20%

  async init(): Promise<void> {
    if (!('getBattery' in navigator)) return;

    try {
      const battery = await (navigator as any).getBattery();
      
      this.updateState(battery);
      
      battery.addEventListener('chargingchange', () => this.updateState(battery));
      battery.addEventListener('levelchange', () => this.updateState(battery));
    } catch {
      // API not available
    }
  }

  private updateState(battery: any): void {
    this.battery = {
      level: battery.level,
      charging: battery.charging,
      chargingTime: battery.chargingTime,
      dischargingTime: battery.dischargingTime,
    };
    
    this.notifyListeners();
    this.applyOptimizations();
  }

  private applyOptimizations(): void {
    if (!this.battery) return;

    const isLowBattery = this.battery.level < this.lowBatteryThreshold && !this.battery.charging;
    
    document.documentElement.classList.toggle('low-battery', isLowBattery);
    document.documentElement.setAttribute('data-battery', isLowBattery ? 'low' : 'normal');
  }

  private notifyListeners(): void {
    if (!this.battery) return;
    this.listeners.forEach(fn => fn(this.battery!));
  }

  subscribe(fn: (state: BatteryState) => void): () => void {
    this.listeners.add(fn);
    if (this.battery) fn(this.battery);
    return () => this.listeners.delete(fn);
  }

  isLowBattery(): boolean {
    return this.battery 
      ? this.battery.level < this.lowBatteryThreshold && !this.battery.charging 
      : false;
  }

  getLevel(): number | null {
    return this.battery?.level ?? null;
  }
}

export const batteryManager = new BatteryManager();
```

### CSS for Low Battery Mode

```css
/* Low battery optimizations */
[data-battery="low"] {
  /* Disable non-essential animations */
  --animation-duration: 0s;
  --transition-duration: 0s;
}

[data-battery="low"] *,
[data-battery="low"] *::before,
[data-battery="low"] *::after {
  animation-duration: 0.001ms !important;
  animation-iteration-count: 1 !important;
  transition-duration: 0.001ms !important;
}

/* Show low battery indicator */
[data-battery="low"] .battery-indicator {
  display: flex;
}

/* Disable autoplay videos */
[data-battery="low"] video[autoplay] {
  display: none;
}

/* Reduce image quality (via srcset) */
[data-battery="low"] img {
  image-rendering: pixelated;
}
```

### React Hook

```tsx
// useBattery.ts
import { useState, useEffect } from 'react';
import { batteryManager, BatteryState } from './batteryAwareness';

export function useBattery() {
  const [state, setState] = useState<BatteryState | null>(null);

  useEffect(() => {
    batteryManager.init();
    return batteryManager.subscribe(setState);
  }, []);

  return {
    level: state?.level,
    charging: state?.charging,
    isLow: batteryManager.isLowBattery(),
    isSupported: state !== null,
  };
}

// Usage
function VideoPlayer({ src }: { src: string }) {
  const { isLow, charging } = useBattery();

  // Don't autoplay on low battery
  const shouldAutoplay = !isLow || charging;

  return (
    <video 
      src={src} 
      autoPlay={shouldAutoplay}
      controls
    >
      {isLow && (
        <p className="battery-notice">
          Autoplay disabled to save battery
        </p>
      )}
    </video>
  );
}
```

---

## LIGHT SENSOR ADAPTIVE THEME

Automatically adjust theme based on ambient light:

```ts
// lightSensor.ts
class AmbientLightManager {
  private sensor: any = null;
  private threshold = {
    dark: 50,    // lux
    dim: 200,
    normal: 500,
    bright: 1000,
  };
  private listeners: Set<(illuminance: number) => void> = new Set();

  async init(): Promise<boolean> {
    if (!('AmbientLightSensor' in window)) return false;

    try {
      // Request permission
      const permission = await navigator.permissions.query({
        name: 'ambient-light-sensor' as PermissionName,
      });

      if (permission.state === 'denied') return false;

      this.sensor = new (window as any).AmbientLightSensor({ frequency: 1 });
      
      this.sensor.addEventListener('reading', () => {
        this.handleReading(this.sensor.illuminance);
      });
      
      this.sensor.addEventListener('error', (e: any) => {
        console.warn('Light sensor error:', e.error);
      });
      
      this.sensor.start();
      return true;
    } catch {
      return false;
    }
  }

  private handleReading(illuminance: number): void {
    this.notifyListeners(illuminance);
    this.applyTheme(illuminance);
  }

  private applyTheme(illuminance: number): void {
    // Only auto-adjust if user hasn't set manual preference
    if (localStorage.getItem('theme-manual') === 'true') return;

    let theme: string;
    if (illuminance < this.threshold.dark) {
      theme = 'dark';
    } else if (illuminance < this.threshold.dim) {
      theme = 'dim';
    } else if (illuminance > this.threshold.bright) {
      theme = 'light-high-contrast';
    } else {
      theme = 'light';
    }

    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.setAttribute('data-ambient-light', String(Math.round(illuminance)));
  }

  private notifyListeners(illuminance: number): void {
    this.listeners.forEach(fn => fn(illuminance));
  }

  subscribe(fn: (illuminance: number) => void): () => void {
    this.listeners.add(fn);
    return () => this.listeners.delete(fn);
  }

  stop(): void {
    this.sensor?.stop();
  }
}

export const lightSensor = new AmbientLightManager();
```

### CSS Variables for Light Levels

```css
/* Adaptive brightness based on ambient light */
:root {
  --ambient-brightness: 1;
}

[data-theme="dark"] {
  --ambient-brightness: 0.9;
}

[data-theme="dim"] {
  --ambient-brightness: 0.95;
}

[data-theme="light-high-contrast"] {
  --ambient-brightness: 1.1;
  --text-1: #000000;
  --surface-1: #ffffff;
}

/* Apply to images for comfortable viewing */
img {
  filter: brightness(var(--ambient-brightness));
}

/* Reduce blue light in dim conditions */
[data-theme="dim"] {
  filter: sepia(0.1) saturate(0.9);
}
```

---

## NETWORK-ADAPTIVE LOADING

Adjust quality based on connection speed:

```ts
// networkAwareness.ts
interface NetworkInfo {
  effectiveType: '4g' | '3g' | '2g' | 'slow-2g';
  downlink: number;      // Mbps
  rtt: number;           // Round-trip time in ms
  saveData: boolean;     // Data saver enabled
}

class NetworkManager {
  private connection: any = null;
  private listeners: Set<(info: NetworkInfo) => void> = new Set();

  init(): void {
    this.connection = (navigator as any).connection ||
                      (navigator as any).mozConnection ||
                      (navigator as any).webkitConnection;

    if (!this.connection) return;

    this.connection.addEventListener('change', () => this.notifyListeners());
    this.notifyListeners();
    this.applyOptimizations();
  }

  private getInfo(): NetworkInfo | null {
    if (!this.connection) return null;

    return {
      effectiveType: this.connection.effectiveType || '4g',
      downlink: this.connection.downlink || 10,
      rtt: this.connection.rtt || 50,
      saveData: this.connection.saveData || false,
    };
  }

  private applyOptimizations(): void {
    const info = this.getInfo();
    if (!info) return;

    const isSlowConnection = ['2g', 'slow-2g'].includes(info.effectiveType);
    const isSaveData = info.saveData;

    document.documentElement.classList.toggle('slow-connection', isSlowConnection);
    document.documentElement.classList.toggle('save-data', isSaveData);
    document.documentElement.setAttribute('data-network', info.effectiveType);
  }

  private notifyListeners(): void {
    const info = this.getInfo();
    if (info) {
      this.listeners.forEach(fn => fn(info));
    }
  }

  subscribe(fn: (info: NetworkInfo) => void): () => void {
    this.listeners.add(fn);
    const info = this.getInfo();
    if (info) fn(info);
    return () => this.listeners.delete(fn);
  }

  shouldLoadHeavyContent(): boolean {
    const info = this.getInfo();
    if (!info) return true;
    return !info.saveData && !['2g', 'slow-2g'].includes(info.effectiveType);
  }

  getImageQuality(): 'high' | 'medium' | 'low' {
    const info = this.getInfo();
    if (!info) return 'high';

    if (info.saveData || info.effectiveType === 'slow-2g') return 'low';
    if (info.effectiveType === '2g' || info.effectiveType === '3g') return 'medium';
    return 'high';
  }
}

export const networkManager = new NetworkManager();
```

### Adaptive Image Loading

```tsx
// AdaptiveImage.tsx
function AdaptiveImage({ 
  src, 
  alt, 
  lowSrc, 
  mediumSrc 
}: Props) {
  const quality = networkManager.getImageQuality();
  const { isLow: lowBattery } = useBattery();

  // Determine best source
  const imageSrc = useMemo(() => {
    if (lowBattery || quality === 'low') return lowSrc || src;
    if (quality === 'medium') return mediumSrc || src;
    return src;
  }, [quality, lowBattery, src, lowSrc, mediumSrc]);

  return (
    <img 
      src={imageSrc} 
      alt={alt}
      loading="lazy"
      decoding="async"
    />
  );
}

// Or with srcset
function ResponsiveImage({ baseSrc, alt }: Props) {
  const quality = networkManager.getImageQuality();

  return (
    <picture>
      {quality === 'high' && (
        <source 
          srcSet={`${baseSrc}-2x.avif 2x, ${baseSrc}.avif 1x`}
          type="image/avif"
        />
      )}
      {quality !== 'low' && (
        <source 
          srcSet={`${baseSrc}.webp`}
          type="image/webp"
        />
      )}
      <img 
        src={`${baseSrc}-low.jpg`} 
        alt={alt}
        loading="lazy"
      />
    </picture>
  );
}
```

### CSS for Slow Connections

```css
/* Data saver mode */
.save-data img:not([data-essential]),
.save-data video,
.save-data iframe[src*="youtube"],
.save-data iframe[src*="vimeo"] {
  display: none;
}

.save-data .media-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--surface-2);
  border: 2px dashed var(--border);
}

.save-data .media-placeholder::after {
  content: 'Data saver enabled. Tap to load.';
  color: var(--text-muted);
}

/* Slow connection optimizations */
.slow-connection * {
  animation: none !important;
  transition: none !important;
}

.slow-connection .hero-video {
  display: none;
}

.slow-connection .hero-video-fallback {
  display: block;
}
```

---

## DEVICE MOTION AWARENESS

Detect device orientation and motion:

```ts
// motionAwareness.ts
interface MotionState {
  isMoving: boolean;
  orientation: 'portrait' | 'landscape';
  tiltX: number;  // -90 to 90
  tiltY: number;
}

class MotionManager {
  private state: MotionState = {
    isMoving: false,
    orientation: 'portrait',
    tiltX: 0,
    tiltY: 0,
  };
  private lastMotion = 0;
  private motionThreshold = 15;

  async init(): Promise<boolean> {
    // iOS requires permission
    if (typeof DeviceMotionEvent !== 'undefined' && 
        typeof (DeviceMotionEvent as any).requestPermission === 'function') {
      try {
        const permission = await (DeviceMotionEvent as any).requestPermission();
        if (permission !== 'granted') return false;
      } catch {
        return false;
      }
    }

    window.addEventListener('devicemotion', this.handleMotion.bind(this));
    window.addEventListener('deviceorientation', this.handleOrientation.bind(this));
    
    return true;
  }

  private handleMotion(event: DeviceMotionEvent): void {
    const { x, y, z } = event.accelerationIncludingGravity || {};
    if (x === null || y === null || z === null) return;

    const totalAcceleration = Math.sqrt(x * x + y * y + z * z);
    const isMoving = Math.abs(totalAcceleration - 9.81) > this.motionThreshold;

    if (isMoving !== this.state.isMoving) {
      this.state.isMoving = isMoving;
      this.applyMotionStyles();
    }
  }

  private handleOrientation(event: DeviceOrientationEvent): void {
    this.state.tiltX = event.gamma || 0;  // Left/right tilt
    this.state.tiltY = event.beta || 0;   // Front/back tilt
    
    // Update CSS variables for parallax effects
    document.documentElement.style.setProperty('--tilt-x', `${this.state.tiltX}deg`);
    document.documentElement.style.setProperty('--tilt-y', `${this.state.tiltY}deg`);
  }

  private applyMotionStyles(): void {
    // Disable delicate UI when device is moving
    document.documentElement.classList.toggle('device-moving', this.state.isMoving);
  }

  isMoving(): boolean {
    return this.state.isMoving;
  }

  getTilt(): { x: number; y: number } {
    return { x: this.state.tiltX, y: this.state.tiltY };
  }
}

export const motionManager = new MotionManager();
```

### Tilt-Responsive Parallax

```css
/* Device tilt parallax */
@media (prefers-reduced-motion: no-preference) {
  .tilt-parallax {
    transform: 
      perspective(1000px)
      rotateX(calc(var(--tilt-y, 0deg) * 0.1))
      rotateY(calc(var(--tilt-x, 0deg) * 0.1));
    transition: transform 0.1s ease-out;
  }
}

/* Disable when device is moving */
.device-moving .tilt-parallax {
  transform: none;
}

/* Alternative: shadow that follows tilt */
.tilt-shadow {
  box-shadow: 
    calc(var(--tilt-x, 0) * -0.5px) 
    calc(var(--tilt-y, 0) * -0.5px) 
    30px 
    rgba(0, 0, 0, 0.15);
}
```

---

## UNIFIED ENVIRONMENT HOOK

```tsx
// useEnvironment.ts
import { useBattery } from './useBattery';
import { useNetwork } from './useNetwork';
import { useAmbientLight } from './useAmbientLight';

export function useEnvironment() {
  const battery = useBattery();
  const network = useNetwork();
  const light = useAmbientLight();

  const shouldReduceMotion = 
    battery.isLow || 
    network.saveData || 
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const shouldReduceQuality = 
    battery.isLow || 
    network.quality !== 'high';

  const effectiveTheme = 
    light.illuminance !== null
      ? light.illuminance < 50 ? 'dark' : 'light'
      : document.documentElement.getAttribute('data-theme') || 'light';

  return {
    battery,
    network,
    light,
    
    // Computed recommendations
    shouldReduceMotion,
    shouldReduceQuality,
    effectiveTheme,
    
    // Quick checks
    isOptimalConditions: !battery.isLow && network.quality === 'high',
    isPowerSaveMode: battery.isLow || network.saveData,
  };
}

// Usage
function HeroSection() {
  const { shouldReduceMotion, shouldReduceQuality, isPowerSaveMode } = useEnvironment();

  return (
    <section className="hero">
      {isPowerSaveMode ? (
        <img src="/hero-static.webp" alt="Hero" />
      ) : (
        <video 
          src="/hero-video.mp4" 
          autoPlay={!shouldReduceMotion}
          muted 
          loop 
        />
      )}
    </section>
  );
}
```

---

## FEATURE DETECTION UTILITIES

```ts
// featureDetection.ts
export const supports = {
  battery: () => 'getBattery' in navigator,
  networkInfo: () => 'connection' in navigator,
  ambientLight: () => 'AmbientLightSensor' in window,
  deviceMotion: () => 'DeviceMotionEvent' in window,
  deviceOrientation: () => 'DeviceOrientationEvent' in window,
  vibration: () => 'vibrate' in navigator,
  geolocation: () => 'geolocation' in navigator,
  webShare: () => 'share' in navigator,
  
  // Compound checks
  fullAmbientAwareness: () => 
    supports.battery() && 
    supports.networkInfo() && 
    supports.ambientLight(),
};

// Log capabilities on init
export function logCapabilities(): void {
  console.group('Device Capabilities');
  Object.entries(supports).forEach(([name, check]) => {
    if (typeof check === 'function' && name !== 'fullAmbientAwareness') {
      console.log(`${name}: ${check() ? '✅' : '❌'}`);
    }
  });
  console.groupEnd();
}
```

---

## CHECKLIST

```
□ Battery API used only for enhancement (not required)
□ Network type informs quality, not functionality
□ Light sensor respects manual theme override
□ All sensor polling is throttled
□ Graceful fallback when APIs unavailable
□ User can override all automatic behaviors
□ No sensor data sent to server (privacy)
□ Reduced motion preference takes priority
□ Data saver mode disables non-essential media
□ Low battery mode disables animations
□ CSS variables expose sensor values for styling
□ Feature detection before API usage
```
