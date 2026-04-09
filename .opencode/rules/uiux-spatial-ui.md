---
description: WebGPU, Three.js, and spatial UI patterns for immersive 3D web experiences. Product viewers, depth layers, and parallax beyond the basic.
---

# Spatial UI & 3D Web Experiences

> Flat is dead. The web is going spatial. This file covers WebGPU, Three.js,
> 3D product viewers, depth-layered interfaces, and parallax that actually
> impresses — not the 2015 "background moves slower" kind.

---

## CRITICAL RULES

1. **Performance first** — 3D must not tank mobile. Use LOD (level of detail), lazy-load models.
2. **Graceful degradation** — Always provide 2D fallback for unsupported browsers.
3. **Accessibility** — 3D viewers need keyboard controls, alt descriptions, reduced-motion fallback.
4. **Design tokens** — All colors, shadows, and UI overlays use tokens.

---

## WHEN TO USE SPATIAL UI

| Use Case | Recommended Approach |
|----------|---------------------|
| Product viewer (e-commerce) | Three.js + GLB model |
| Hero visual impact | CSS 3D transforms + depth layers |
| Data visualization | Three.js or WebGPU for large datasets |
| Interactive storytelling | Scroll-linked 3D scenes |
| Gaming/immersive | WebGPU (modern) or Three.js |

**Never use 3D for:**
- Navigation menus
- Form inputs
- Text content
- Anything that needs to be accessible by default

---

## THREE.JS PRODUCT VIEWER

```tsx
// ProductViewer.tsx — React + Three.js + React Three Fiber
import { Canvas, useFrame } from '@react-three/fiber';
import { OrbitControls, useGLTF, Environment, ContactShadows } from '@react-three/drei';
import { Suspense, useRef } from 'react';

function Model({ url }: { url: string }) {
  const { scene } = useGLTF(url);
  const ref = useRef<THREE.Group>(null);
  
  // Respect reduced motion
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  useFrame((state) => {
    if (ref.current && !prefersReducedMotion) {
      // Subtle idle rotation
      ref.current.rotation.y = Math.sin(state.clock.elapsedTime * 0.3) * 0.1;
    }
  });

  return <primitive ref={ref} object={scene} scale={1} />;
}

function LoadingFallback() {
  return (
    <mesh>
      <boxGeometry args={[1, 1, 1]} />
      <meshStandardMaterial color="var(--surface-2)" wireframe />
    </mesh>
  );
}

export function ProductViewer({ modelUrl, fallbackImage }: Props) {
  const supportsWebGL = !!document.createElement('canvas').getContext('webgl2');

  if (!supportsWebGL) {
    return (
      <div className="product-viewer-fallback">
        <img src={fallbackImage} alt="Product view" loading="lazy" />
        <p className="text-muted">3D view not supported in this browser</p>
      </div>
    );
  }

  return (
    <div className="product-viewer" role="img" aria-label="Interactive 3D product viewer. Use mouse to rotate, scroll to zoom.">
      <Canvas
        camera={{ position: [0, 0, 4], fov: 45 }}
        dpr={[1, 2]} // Limit pixel ratio for performance
      >
        <ambientLight intensity={0.5} />
        <spotLight position={[10, 10, 10]} angle={0.15} penumbra={1} />
        
        <Suspense fallback={<LoadingFallback />}>
          <Model url={modelUrl} />
          <Environment preset="studio" />
          <ContactShadows position={[0, -1, 0]} opacity={0.4} blur={2} />
        </Suspense>
        
        <OrbitControls 
          enablePan={false}
          minDistance={2}
          maxDistance={8}
          minPolarAngle={Math.PI / 4}
          maxPolarAngle={Math.PI / 2}
        />
      </Canvas>
      
      {/* Keyboard instructions for accessibility */}
      <div className="sr-only" aria-live="polite">
        Use arrow keys to rotate. Plus and minus to zoom.
      </div>
    </div>
  );
}
```

```css
/* product-viewer.css */
.product-viewer {
  position: relative;
  width: 100%;
  aspect-ratio: 1 / 1;
  background: var(--surface-1);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.product-viewer canvas {
  touch-action: none; /* Prevent scroll hijacking on mobile */
}

.product-viewer-fallback {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--space-4);
  padding: var(--space-8);
  background: var(--surface-1);
  border-radius: var(--radius-lg);
}

.product-viewer-fallback img {
  max-width: 100%;
  height: auto;
}

@media (prefers-reduced-motion: reduce) {
  .product-viewer canvas {
    /* Static view when reduced motion preferred */
  }
}
```

---

## DEPTH-LAYERED HERO (CSS 3D)

No Three.js required — pure CSS for subtle depth effect:

```html
<section class="hero-depth" aria-label="Welcome section">
  <div class="hero-depth__layer hero-depth__layer--back">
    <div class="hero-depth__shape hero-depth__shape--circle"></div>
    <div class="hero-depth__shape hero-depth__shape--blob"></div>
  </div>
  
  <div class="hero-depth__layer hero-depth__layer--mid">
    <img src="/images/product-float.webp" alt="" class="hero-depth__product" 
         width="600" height="600" loading="eager" fetchpriority="high">
  </div>
  
  <div class="hero-depth__layer hero-depth__layer--front">
    <h1 class="hero-depth__title">The Future is Spatial</h1>
    <p class="hero-depth__subtitle">Experience design in three dimensions</p>
    <a href="#explore" class="btn btn--primary">Explore</a>
  </div>
</section>
```

```css
.hero-depth {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  perspective: 1000px;
  overflow: hidden;
}

.hero-depth__layer {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.1s ease-out;
}

.hero-depth__layer--back {
  transform: translateZ(-200px) scale(1.4);
}

.hero-depth__layer--mid {
  transform: translateZ(-100px) scale(1.2);
}

.hero-depth__layer--front {
  position: relative;
  transform: translateZ(0);
  text-align: center;
  z-index: 10;
}

.hero-depth__shape {
  position: absolute;
  border-radius: 50%;
  filter: blur(60px);
  opacity: 0.6;
}

.hero-depth__shape--circle {
  width: 400px;
  height: 400px;
  background: var(--accent);
  top: 20%;
  left: 10%;
}

.hero-depth__shape--blob {
  width: 300px;
  height: 300px;
  background: var(--accent-secondary);
  bottom: 20%;
  right: 15%;
}

.hero-depth__product {
  max-width: 50vw;
  height: auto;
  filter: drop-shadow(0 40px 80px rgba(0, 0, 0, 0.3));
}

/* Mouse parallax via JS */
@media (prefers-reduced-motion: no-preference) {
  .hero-depth[data-parallax-active] .hero-depth__layer--back {
    transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  }
  .hero-depth[data-parallax-active] .hero-depth__layer--mid {
    transition: transform 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  }
}

@media (prefers-reduced-motion: reduce) {
  .hero-depth__layer {
    transform: none !important;
  }
}
```

```js
// Mouse parallax for depth layers
function initDepthParallax() {
  const hero = document.querySelector('.hero-depth');
  if (!hero || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  
  hero.setAttribute('data-parallax-active', '');
  
  const layers = {
    back: hero.querySelector('.hero-depth__layer--back'),
    mid: hero.querySelector('.hero-depth__layer--mid'),
  };
  
  hero.addEventListener('mousemove', (e) => {
    const rect = hero.getBoundingClientRect();
    const x = (e.clientX - rect.left - rect.width / 2) / rect.width;
    const y = (e.clientY - rect.top - rect.height / 2) / rect.height;
    
    layers.back.style.transform = `translateZ(-200px) scale(1.4) translate(${x * 30}px, ${y * 30}px)`;
    layers.mid.style.transform = `translateZ(-100px) scale(1.2) translate(${x * 15}px, ${y * 15}px)`;
  });
  
  hero.addEventListener('mouseleave', () => {
    layers.back.style.transform = 'translateZ(-200px) scale(1.4)';
    layers.mid.style.transform = 'translateZ(-100px) scale(1.2)';
  });
}
```

---

## SCROLL-LINKED 3D SCENE

Transform scroll position into 3D camera movement:

```tsx
// ScrollScene.tsx
import { Canvas, useThree, useFrame } from '@react-three/fiber';
import { useScroll } from '@react-three/drei';
import { useRef } from 'react';
import * as THREE from 'three';

function CameraRig() {
  const scroll = useScroll();
  const { camera } = useThree();
  
  useFrame(() => {
    const offset = scroll.offset; // 0 to 1
    
    // Move camera along a path based on scroll
    camera.position.z = 10 - offset * 8;
    camera.position.y = offset * 2;
    camera.lookAt(0, 0, 0);
  });
  
  return null;
}

function FloatingObjects() {
  const groupRef = useRef<THREE.Group>(null);
  const scroll = useScroll();
  
  useFrame(() => {
    if (!groupRef.current) return;
    groupRef.current.rotation.y = scroll.offset * Math.PI * 2;
  });
  
  return (
    <group ref={groupRef}>
      {[...Array(20)].map((_, i) => (
        <mesh
          key={i}
          position={[
            Math.sin(i * 0.5) * 3,
            Math.cos(i * 0.3) * 2,
            Math.sin(i * 0.7) * 3,
          ]}
        >
          <icosahedronGeometry args={[0.2, 0]} />
          <meshStandardMaterial color="var(--accent)" metalness={0.8} roughness={0.2} />
        </mesh>
      ))}
    </group>
  );
}

export function ScrollScene() {
  return (
    <div className="scroll-scene">
      <Canvas>
        <ScrollControls pages={3} damping={0.1}>
          <ambientLight intensity={0.5} />
          <pointLight position={[10, 10, 10]} />
          
          <CameraRig />
          <FloatingObjects />
          
          {/* Content overlays */}
          <Html center position={[0, 0, 0]}>
            <div className="scroll-scene__content">
              <h2>Scroll to Explore</h2>
            </div>
          </Html>
        </ScrollControls>
      </Canvas>
    </div>
  );
}
```

---

## WEBGPU (BLEEDING EDGE)

For massive particle systems or compute-heavy visuals:

```js
// Check WebGPU support
async function initWebGPU() {
  if (!navigator.gpu) {
    console.warn('WebGPU not supported, falling back to WebGL');
    return initWebGLFallback();
  }
  
  const adapter = await navigator.gpu.requestAdapter();
  if (!adapter) {
    console.warn('No GPU adapter found');
    return initWebGLFallback();
  }
  
  const device = await adapter.requestDevice();
  
  // WebGPU rendering logic...
  return { adapter, device };
}

// Feature detection wrapper
export async function init3DScene(container: HTMLElement) {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  if (prefersReducedMotion) {
    // Show static image instead
    container.innerHTML = `<img src="/static-preview.webp" alt="Scene preview" class="static-fallback">`;
    return;
  }
  
  try {
    const gpu = await initWebGPU();
    // Initialize scene...
  } catch (error) {
    console.error('3D initialization failed:', error);
    container.innerHTML = `<div class="error-fallback">3D view unavailable</div>`;
  }
}
```

---

## PERFORMANCE CHECKLIST

```
□ GLB models are optimized (Draco compression, <2MB)
□ Textures are power-of-2 and compressed (KTX2/Basis)
□ LOD implemented for complex models
□ Canvas DPR capped at 2 (dpr={[1, 2]})
□ Lazy-load 3D scenes below the fold
□ Static fallback for reduced-motion users
□ 2D fallback for unsupported browsers
□ Keyboard controls for orbit/zoom
□ Touch gestures work on mobile
□ No scroll hijacking — 3D is contained
□ GPU memory released on unmount
```

---

## ACCESSIBILITY FOR 3D

```html
<!-- Always provide context -->
<div 
  class="product-viewer"
  role="img"
  aria-label="Interactive 3D model of [product name]. Drag to rotate, pinch to zoom."
>
  <canvas></canvas>
  
  <!-- Keyboard instructions -->
  <div class="viewer-controls" aria-describedby="viewer-help">
    <button aria-label="Rotate left">←</button>
    <button aria-label="Rotate right">→</button>
    <button aria-label="Zoom in">+</button>
    <button aria-label="Zoom out">−</button>
    <button aria-label="Reset view">Reset</button>
  </div>
  
  <p id="viewer-help" class="sr-only">
    Use arrow keys to rotate the model. Plus and minus keys to zoom.
    Press R to reset the view.
  </p>
</div>
```

---

## DON'T BE THIS DEVELOPER

```
❌ 3D background on the entire page (tanks performance)
❌ Scroll hijacking for "immersive experience" (users hate it)
❌ Auto-rotating models with no pause control
❌ 50MB unoptimized GLB files
❌ No fallback for Safari/Firefox without WebGPU
❌ 3D text that's unreadable
❌ Forcing 3D on mobile without considering battery
```
