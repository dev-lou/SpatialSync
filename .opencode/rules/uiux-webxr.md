---
description: WebXR patterns for AR try-on, VR experiences, immersive product views, and spatial web interfaces. The next dimension of web UI.
---

# WebXR — AR/VR Web Experiences

> The web is going spatial. This file covers WebXR fundamentals, AR try-on,
> VR product experiences, and immersive interfaces — with graceful fallbacks
> for the 99% of users who don't have headsets.

---

## CRITICAL RULES

1. **Fallback first** — Every XR feature needs a 2D alternative.
2. **Performance** — XR must hit 72fps minimum. 90fps preferred.
3. **Motion sickness** — Follow comfort guidelines (no artificial locomotion).
4. **Accessibility** — Provide non-XR alternatives for all features.
5. **Progressive enhancement** — Detect support, enhance gracefully.

---

## WEBXR SUPPORT DETECTION

```ts
// xrSupport.ts
interface XRSupport {
  immersiveVR: boolean;    // Full VR headset
  immersiveAR: boolean;    // AR headset or phone
  inlineAR: boolean;       // AR on phone screen
}

export async function checkXRSupport(): Promise<XRSupport> {
  const support: XRSupport = {
    immersiveVR: false,
    immersiveAR: false,
    inlineAR: false,
  };

  if (!('xr' in navigator)) {
    return support;
  }

  try {
    support.immersiveVR = await navigator.xr!.isSessionSupported('immersive-vr');
    support.immersiveAR = await navigator.xr!.isSessionSupported('immersive-ar');
    support.inlineAR = await navigator.xr!.isSessionSupported('inline');
  } catch (e) {
    console.warn('WebXR check failed:', e);
  }

  return support;
}

// React hook
export function useXRSupport() {
  const [support, setSupport] = useState<XRSupport | null>(null);

  useEffect(() => {
    checkXRSupport().then(setSupport);
  }, []);

  return support;
}
```

---

## AR PRODUCT TRY-ON

### Model Viewer (Quick Start)

Use Google's `<model-viewer>` for easy AR:

```html
<!-- Include the library -->
<script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>

<!-- AR-enabled 3D model -->
<model-viewer
  src="/models/product.glb"
  alt="Product name - 3D view"
  ar
  ar-modes="webxr scene-viewer quick-look"
  camera-controls
  shadow-intensity="1"
  poster="/images/product-poster.webp"
  loading="lazy"
>
  <!-- AR button (auto-shown when supported) -->
  <button slot="ar-button" class="ar-button">
    View in your space
  </button>
  
  <!-- Fallback for no AR -->
  <div slot="poster" class="poster">
    <img src="/images/product-poster.webp" alt="Product preview">
    <p>Loading 3D model...</p>
  </div>
</model-viewer>
```

```css
/* AR button styling */
model-viewer {
  width: 100%;
  height: 400px;
  background: var(--surface-2);
  border-radius: var(--radius-lg);
}

.ar-button {
  position: absolute;
  bottom: 16px;
  right: 16px;
  padding: 12px 24px;
  background: var(--accent);
  color: var(--accent-contrast);
  border: none;
  border-radius: var(--radius-md);
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.ar-button::before {
  content: '📱';
}

/* Hide AR button if not supported */
model-viewer:not([ar-status="session-started"]) .ar-button {
  display: flex;
}
```

### React Component

```tsx
// ARProductViewer.tsx
import '@google/model-viewer';

interface ARProductViewerProps {
  modelUrl: string;
  posterUrl: string;
  alt: string;
  onARStart?: () => void;
  onAREnd?: () => void;
}

export function ARProductViewer({
  modelUrl,
  posterUrl,
  alt,
  onARStart,
  onAREnd,
}: ARProductViewerProps) {
  const modelRef = useRef<HTMLElement>(null);
  const xrSupport = useXRSupport();

  useEffect(() => {
    const model = modelRef.current;
    if (!model) return;

    const handleARStatus = (e: any) => {
      if (e.detail.status === 'session-started') {
        onARStart?.();
      } else if (e.detail.status === 'not-presenting') {
        onAREnd?.();
      }
    };

    model.addEventListener('ar-status', handleARStatus);
    return () => model.removeEventListener('ar-status', handleARStatus);
  }, [onARStart, onAREnd]);

  return (
    <div className="ar-product-viewer">
      {/* @ts-ignore - model-viewer custom element */}
      <model-viewer
        ref={modelRef}
        src={modelUrl}
        alt={alt}
        ar
        ar-modes="webxr scene-viewer quick-look"
        camera-controls
        touch-action="pan-y"
        shadow-intensity="1"
        poster={posterUrl}
        loading="lazy"
      >
        <button slot="ar-button" className="ar-button">
          📱 View in your space
        </button>
      </model-viewer>

      {/* AR instructions (shown when AR not available) */}
      {xrSupport && !xrSupport.inlineAR && !xrSupport.immersiveAR && (
        <div className="ar-fallback">
          <p>AR preview not available on this device.</p>
          <p>Try viewing on a mobile device for the full experience.</p>
        </div>
      )}
    </div>
  );
}
```

---

## AR FACE FILTERS (Glasses, Makeup)

```tsx
// ARFaceTryOn.tsx
import { FaceMesh } from '@mediapipe/face_mesh';
import { Camera } from '@mediapipe/camera_utils';

interface ARFaceTryOnProps {
  overlayImage: string;  // Glasses, hat, etc.
  type: 'glasses' | 'hat' | 'earrings' | 'makeup';
}

export function ARFaceTryOn({ overlayImage, type }: ARFaceTryOnProps) {
  const videoRef = useRef<HTMLVideoElement>(null);
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const video = videoRef.current;
    const canvas = canvasRef.current;
    if (!video || !canvas) return;

    const faceMesh = new FaceMesh({
      locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`,
    });

    faceMesh.setOptions({
      maxNumFaces: 1,
      refineLandmarks: true,
      minDetectionConfidence: 0.5,
      minTrackingConfidence: 0.5,
    });

    const overlay = new Image();
    overlay.src = overlayImage;

    faceMesh.onResults((results) => {
      const ctx = canvas.getContext('2d');
      if (!ctx || !results.multiFaceLandmarks[0]) return;

      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

      const landmarks = results.multiFaceLandmarks[0];

      // Position overlay based on type
      if (type === 'glasses') {
        const leftEye = landmarks[33];
        const rightEye = landmarks[263];
        const eyeWidth = Math.abs(rightEye.x - leftEye.x) * canvas.width * 1.5;
        const eyeCenterX = (leftEye.x + rightEye.x) / 2 * canvas.width;
        const eyeCenterY = (leftEye.y + rightEye.y) / 2 * canvas.height;

        ctx.drawImage(
          overlay,
          eyeCenterX - eyeWidth / 2,
          eyeCenterY - eyeWidth / 4,
          eyeWidth,
          eyeWidth / 2
        );
      }
      // Add other types (hat, earrings, etc.)
    });

    const camera = new Camera(video, {
      onFrame: async () => {
        await faceMesh.send({ image: video });
      },
      width: 640,
      height: 480,
    });

    camera.start()
      .then(() => setIsLoading(false))
      .catch((err) => setError('Camera access denied'));

    return () => {
      camera.stop();
      faceMesh.close();
    };
  }, [overlayImage, type]);

  if (error) {
    return (
      <div className="ar-error">
        <p>{error}</p>
        <p>Please allow camera access to try on this item.</p>
      </div>
    );
  }

  return (
    <div className="ar-face-tryon">
      {isLoading && (
        <div className="ar-loading">
          <Spinner />
          <p>Starting camera...</p>
        </div>
      )}
      
      <video ref={videoRef} style={{ display: 'none' }} />
      <canvas ref={canvasRef} width={640} height={480} />
      
      <button className="ar-capture-btn" onClick={capturePhoto}>
        📸 Take Photo
      </button>
    </div>
  );
}
```

---

## VR PRODUCT EXPERIENCE

### Immersive VR Showroom

```tsx
// VRShowroom.tsx
import { VRButton } from 'three/examples/jsm/webxr/VRButton';
import { Canvas, useThree, useFrame } from '@react-three/fiber';
import { Controllers, Hands, XR, Interactive } from '@react-three/xr';

function VRScene({ products }: { products: Product[] }) {
  return (
    <XR>
      <Controllers />
      <Hands />
      
      {/* Environment */}
      <ambientLight intensity={0.5} />
      <pointLight position={[10, 10, 10]} />
      <Environment preset="apartment" />
      
      {/* Products arranged in a circle */}
      {products.map((product, i) => {
        const angle = (i / products.length) * Math.PI * 2;
        const radius = 3;
        
        return (
          <Interactive
            key={product.id}
            onSelect={() => selectProduct(product)}
            onHover={() => highlightProduct(product)}
          >
            <ProductModel
              url={product.modelUrl}
              position={[
                Math.sin(angle) * radius,
                1,
                Math.cos(angle) * radius,
              ]}
              rotation={[0, -angle + Math.PI / 2, 0]}
            />
          </Interactive>
        );
      })}
      
      {/* Floor */}
      <mesh rotation={[-Math.PI / 2, 0, 0]} position={[0, 0, 0]}>
        <planeGeometry args={[20, 20]} />
        <meshStandardMaterial color="#333" />
      </mesh>
    </XR>
  );
}

export function VRShowroom({ products }: { products: Product[] }) {
  const xrSupport = useXRSupport();

  return (
    <div className="vr-showroom">
      <Canvas>
        <VRScene products={products} />
      </Canvas>
      
      {xrSupport?.immersiveVR ? (
        <button className="vr-button" onClick={enterVR}>
          🥽 Enter VR
        </button>
      ) : (
        <p className="vr-fallback">
          VR headset not detected. Use mouse/touch to explore.
        </p>
      )}
    </div>
  );
}
```

---

## AR PLACEMENT (Furniture, Decor)

```tsx
// ARFurniturePlacement.tsx
export function ARFurniturePlacement({ modelUrl }: { modelUrl: string }) {
  const [session, setSession] = useState<XRSession | null>(null);
  const [hitTestSource, setHitTestSource] = useState<XRHitTestSource | null>(null);
  const [placedObjects, setPlacedObjects] = useState<THREE.Object3D[]>([]);

  const startAR = async () => {
    if (!navigator.xr) return;

    const session = await navigator.xr.requestSession('immersive-ar', {
      requiredFeatures: ['hit-test', 'dom-overlay'],
      domOverlay: { root: document.getElementById('ar-overlay')! },
    });

    setSession(session);

    // Set up hit testing for surface detection
    const viewerSpace = await session.requestReferenceSpace('viewer');
    const hitTestSource = await session.requestHitTestSource({
      space: viewerSpace,
    });
    setHitTestSource(hitTestSource);
  };

  const placeObject = (hitPose: XRPose) => {
    const model = loadModel(modelUrl);
    model.position.setFromMatrixPosition(hitPose.transform.matrix);
    setPlacedObjects(prev => [...prev, model]);
  };

  return (
    <div className="ar-furniture">
      <div id="ar-overlay" className="ar-overlay">
        <p>Point your camera at a flat surface</p>
        <button onClick={() => placeObject(currentHit)}>
          Place here
        </button>
      </div>

      <button onClick={startAR} className="ar-start-btn">
        📐 Place in your room
      </button>
    </div>
  );
}
```

---

## GRACEFUL FALLBACKS

Always provide alternatives:

```tsx
// XRWithFallback.tsx
interface XRWithFallbackProps {
  modelUrl: string;
  posterUrl: string;
  alt: string;
}

export function XRWithFallback({ modelUrl, posterUrl, alt }: XRWithFallbackProps) {
  const xrSupport = useXRSupport();
  const [viewMode, setViewMode] = useState<'image' | '3d' | 'ar'>('image');

  // Loading state
  if (xrSupport === null) {
    return <Skeleton className="product-viewer-skeleton" />;
  }

  return (
    <div className="xr-viewer">
      {/* View mode tabs */}
      <div className="xr-viewer__tabs" role="tablist">
        <button
          role="tab"
          aria-selected={viewMode === 'image'}
          onClick={() => setViewMode('image')}
        >
          📷 Images
        </button>
        <button
          role="tab"
          aria-selected={viewMode === '3d'}
          onClick={() => setViewMode('3d')}
        >
          🎲 3D View
        </button>
        {(xrSupport.immersiveAR || xrSupport.inlineAR) && (
          <button
            role="tab"
            aria-selected={viewMode === 'ar'}
            onClick={() => setViewMode('ar')}
          >
            📱 AR
          </button>
        )}
      </div>

      {/* Content based on mode */}
      <div className="xr-viewer__content">
        {viewMode === 'image' && (
          <ImageGallery images={[posterUrl]} alt={alt} />
        )}
        
        {viewMode === '3d' && (
          <model-viewer
            src={modelUrl}
            alt={alt}
            camera-controls
            poster={posterUrl}
          />
        )}
        
        {viewMode === 'ar' && (
          <model-viewer
            src={modelUrl}
            alt={alt}
            ar
            ar-modes="webxr scene-viewer quick-look"
            camera-controls
            poster={posterUrl}
          >
            <button slot="ar-button" className="ar-button">
              View in your space
            </button>
          </model-viewer>
        )}
      </div>

      {/* QR code for mobile AR */}
      {!xrSupport.inlineAR && !xrSupport.immersiveAR && viewMode === 'ar' && (
        <div className="ar-qr-fallback">
          <QRCode value={window.location.href} size={120} />
          <p>Scan with your phone to view in AR</p>
        </div>
      )}
    </div>
  );
}
```

---

## PERFORMANCE OPTIMIZATION

```ts
// xrPerformance.ts
export function optimizeForXR() {
  // Reduce render resolution if needed
  const renderer = getRenderer();
  renderer.xr.setFramebufferScaleFactor(0.75); // 75% resolution

  // Use simpler materials in XR
  scene.traverse((obj) => {
    if (obj instanceof THREE.Mesh && obj.material) {
      const mat = obj.material as THREE.MeshStandardMaterial;
      mat.envMapIntensity = 0.5;
      mat.roughness = Math.max(mat.roughness, 0.3);
    }
  });

  // Reduce shadow quality
  renderer.shadowMap.type = THREE.BasicShadowMap;

  // Limit draw calls
  renderer.setPixelRatio(1);
}

// Monitor frame rate
export function useXRFrameRate() {
  const [fps, setFps] = useState(90);
  
  useFrame((state) => {
    // Track FPS, adjust quality if dropping
    if (fps < 72) {
      reduceQuality();
    }
  });

  return fps;
}
```

---

## ACCESSIBILITY

```tsx
// XRAccessibility.tsx
function XRAccessibility({ arEnabled }: { arEnabled: boolean }) {
  return (
    <>
      {/* Screen reader announcements */}
      <div role="status" className="sr-only" aria-live="polite">
        {arEnabled 
          ? 'AR mode active. Point your camera at a surface to place the object.'
          : '3D view active. Use two fingers to rotate, pinch to zoom.'
        }
      </div>

      {/* Alternative text descriptions */}
      <details className="xr-text-description">
        <summary>Text description of 3D model</summary>
        <p>
          A wooden dining table, 72 inches long, 36 inches wide.
          Rectangular shape with rounded corners. Light oak finish.
          Four tapered legs. Seats 6 people comfortably.
        </p>
      </details>
    </>
  );
}
```

---

## CHECKLIST

```
□ Feature detection before XR features
□ Fallback to 3D viewer when AR unavailable
□ Fallback to images when 3D unavailable
□ QR code for mobile AR access
□ Performance monitoring (72fps minimum)
□ Loading states for model loading
□ Clear AR/VR entry buttons
□ Exit XR button always visible
□ Accessibility text descriptions
□ Reduced motion: no artificial locomotion
□ Models optimized (Draco compression)
□ Poster images while model loads
□ Camera permission handling
□ Works on iOS Safari (Quick Look)
□ Works on Android (Scene Viewer)
```

---

## BROWSER SUPPORT

| Feature | Chrome | Safari | Firefox |
|---------|--------|--------|---------|
| WebXR VR | ✅ | ❌ | ✅ |
| WebXR AR | ✅ (Android) | ❌ | ❌ |
| Quick Look AR | ❌ | ✅ (iOS) | ❌ |
| Scene Viewer | ✅ (Android) | ❌ | ❌ |
| model-viewer | ✅ | ✅ | ✅ |

**Strategy**: Use `<model-viewer>` with `ar-modes="webxr scene-viewer quick-look"` for maximum compatibility.
