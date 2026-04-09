---
description: WebAuthn/passkeys UI patterns, fingerprint and face prompts, fallback flows, and passwordless authentication. The future of login.
---

# Biometric Authentication UI

> Passwords are dying. This file covers WebAuthn/passkeys, biometric prompts,
> passwordless flows, and how to design authentication that's both more secure
> AND easier to use than passwords.

---

## CRITICAL RULES

1. **Always provide fallback** — Not all devices support biometrics.
2. **Explain benefits** — Users need to understand why passkeys are better.
3. **Don't force** — Offer passkeys, don't mandate them.
4. **Clear error states** — Biometric failures need specific guidance.
5. **Cross-device** — Handle device-specific UI gracefully.

---

## WEBAUTHN BASICS

### Support Detection

```ts
// webauthn.ts
export async function checkWebAuthnSupport(): Promise<{
  available: boolean;
  platformAuth: boolean;  // Built-in (fingerprint, Face ID)
  crossPlatform: boolean; // Security keys
}> {
  if (!window.PublicKeyCredential) {
    return { available: false, platformAuth: false, crossPlatform: false };
  }

  const available = true;
  
  // Check for platform authenticator (Touch ID, Face ID, Windows Hello)
  let platformAuth = false;
  try {
    platformAuth = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
  } catch {}

  // Check for conditional mediation (autofill passkeys)
  let conditionalMediation = false;
  try {
    conditionalMediation = await PublicKeyCredential.isConditionalMediationAvailable?.() ?? false;
  } catch {}

  return {
    available,
    platformAuth,
    crossPlatform: true, // Security keys always available if WebAuthn is
  };
}
```

### Registration Flow

```ts
// passkeyRegistration.ts
export async function registerPasskey(
  username: string,
  displayName: string
): Promise<{ success: boolean; error?: string }> {
  try {
    // Get challenge from server
    const options = await fetch('/api/webauthn/register-options', {
      method: 'POST',
      body: JSON.stringify({ username }),
    }).then(r => r.json());

    // Create credential
    const credential = await navigator.credentials.create({
      publicKey: {
        ...options,
        challenge: base64ToBuffer(options.challenge),
        user: {
          ...options.user,
          id: base64ToBuffer(options.user.id),
        },
      },
    }) as PublicKeyCredential;

    // Send to server for verification
    const response = await fetch('/api/webauthn/register-verify', {
      method: 'POST',
      body: JSON.stringify({
        id: credential.id,
        rawId: bufferToBase64(credential.rawId),
        response: {
          clientDataJSON: bufferToBase64(credential.response.clientDataJSON),
          attestationObject: bufferToBase64(
            (credential.response as AuthenticatorAttestationResponse).attestationObject
          ),
        },
        type: credential.type,
      }),
    });

    if (!response.ok) {
      throw new Error('Registration failed');
    }

    return { success: true };
  } catch (error: any) {
    return {
      success: false,
      error: getReadableError(error),
    };
  }
}

function getReadableError(error: Error): string {
  if (error.name === 'NotAllowedError') {
    return 'Registration was cancelled or timed out. Please try again.';
  }
  if (error.name === 'InvalidStateError') {
    return 'A passkey for this account already exists on this device.';
  }
  if (error.name === 'NotSupportedError') {
    return 'Your device doesn\'t support passkeys. Please use a password instead.';
  }
  return 'Something went wrong. Please try again or use a password.';
}
```

---

## PASSKEY REGISTRATION UI

```tsx
// PasskeyRegistration.tsx
interface PasskeyRegistrationProps {
  username: string;
  onSuccess: () => void;
  onSkip: () => void;
}

export function PasskeyRegistration({ 
  username, 
  onSuccess, 
  onSkip 
}: PasskeyRegistrationProps) {
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
  const [error, setError] = useState<string | null>(null);
  const support = useWebAuthnSupport();

  const handleRegister = async () => {
    setStatus('loading');
    setError(null);

    const result = await registerPasskey(username, username);

    if (result.success) {
      setStatus('success');
      setTimeout(onSuccess, 1500);
    } else {
      setStatus('error');
      setError(result.error || 'Registration failed');
    }
  };

  // Don't show if not supported
  if (!support?.available) {
    return null;
  }

  return (
    <div className="passkey-registration">
      <div className="passkey-registration__icon">
        🔐
      </div>

      <h2>Secure your account with a passkey</h2>
      
      <p className="passkey-registration__description">
        Passkeys are a faster, more secure way to sign in. 
        Use your fingerprint, face, or screen lock instead of a password.
      </p>

      <ul className="passkey-registration__benefits">
        <li>✓ No passwords to remember</li>
        <li>✓ Can't be phished or stolen</li>
        <li>✓ Works across your devices</li>
      </ul>

      {status === 'idle' && (
        <div className="passkey-registration__actions">
          <button 
            className="btn btn--primary"
            onClick={handleRegister}
          >
            {support.platformAuth ? (
              <>
                {getPlatformIcon()} Create passkey
              </>
            ) : (
              '🔑 Use security key'
            )}
          </button>
          
          <button 
            className="btn btn--secondary"
            onClick={onSkip}
          >
            Maybe later
          </button>
        </div>
      )}

      {status === 'loading' && (
        <div className="passkey-registration__loading">
          <Spinner />
          <p>Follow the prompts on your device...</p>
        </div>
      )}

      {status === 'success' && (
        <div className="passkey-registration__success">
          <span className="success-icon">✓</span>
          <p>Passkey created successfully!</p>
        </div>
      )}

      {status === 'error' && (
        <div className="passkey-registration__error" role="alert">
          <p>{error}</p>
          <button 
            className="btn btn--secondary"
            onClick={() => setStatus('idle')}
          >
            Try again
          </button>
        </div>
      )}
    </div>
  );
}

function getPlatformIcon(): string {
  const ua = navigator.userAgent;
  if (/iPhone|iPad/.test(ua)) return '👆'; // Face ID / Touch ID
  if (/Mac/.test(ua)) return '👆'; // Touch ID
  if (/Windows/.test(ua)) return '🪟'; // Windows Hello
  if (/Android/.test(ua)) return '👆'; // Fingerprint
  return '🔐';
}
```

---

## PASSKEY LOGIN UI

```tsx
// PasskeyLogin.tsx
export function PasskeyLogin({ onSuccess, onFallback }: PasskeyLoginProps) {
  const [status, setStatus] = useState<'idle' | 'loading' | 'error'>('idle');
  const [error, setError] = useState<string | null>(null);

  const handleLogin = async () => {
    setStatus('loading');
    setError(null);

    try {
      // Get challenge from server
      const options = await fetch('/api/webauthn/login-options').then(r => r.json());

      // Get credential
      const credential = await navigator.credentials.get({
        publicKey: {
          ...options,
          challenge: base64ToBuffer(options.challenge),
          allowCredentials: options.allowCredentials?.map((c: any) => ({
            ...c,
            id: base64ToBuffer(c.id),
          })),
        },
      }) as PublicKeyCredential;

      // Verify with server
      const response = await fetch('/api/webauthn/login-verify', {
        method: 'POST',
        body: JSON.stringify({
          id: credential.id,
          rawId: bufferToBase64(credential.rawId),
          response: {
            clientDataJSON: bufferToBase64(credential.response.clientDataJSON),
            authenticatorData: bufferToBase64(
              (credential.response as AuthenticatorAssertionResponse).authenticatorData
            ),
            signature: bufferToBase64(
              (credential.response as AuthenticatorAssertionResponse).signature
            ),
          },
          type: credential.type,
        }),
      });

      if (!response.ok) {
        throw new Error('Login failed');
      }

      onSuccess();
    } catch (error: any) {
      setStatus('error');
      setError(getReadableLoginError(error));
    }
  };

  return (
    <div className="passkey-login">
      {status === 'idle' && (
        <>
          <button 
            className="btn btn--primary btn--large passkey-login__button"
            onClick={handleLogin}
          >
            <span className="passkey-login__icon">🔐</span>
            Sign in with passkey
          </button>

          <div className="passkey-login__divider">
            <span>or</span>
          </div>

          <button 
            className="btn btn--secondary"
            onClick={onFallback}
          >
            Use password instead
          </button>
        </>
      )}

      {status === 'loading' && (
        <div className="passkey-login__loading">
          <BiometricPrompt />
          <p>Verify your identity...</p>
        </div>
      )}

      {status === 'error' && (
        <div className="passkey-login__error" role="alert">
          <p>{error}</p>
          <div className="passkey-login__actions">
            <button 
              className="btn btn--primary"
              onClick={() => setStatus('idle')}
            >
              Try again
            </button>
            <button 
              className="btn btn--secondary"
              onClick={onFallback}
            >
              Use password
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

function getReadableLoginError(error: Error): string {
  if (error.name === 'NotAllowedError') {
    return 'Authentication cancelled or timed out.';
  }
  if (error.name === 'SecurityError') {
    return 'Security error. Please try again.';
  }
  return 'Something went wrong. Please try again or use your password.';
}
```

---

## BIOMETRIC PROMPT ANIMATION

Visual feedback while waiting for biometric:

```tsx
// BiometricPrompt.tsx
function BiometricPrompt() {
  const [device] = useState(() => detectDevice());

  return (
    <div className="biometric-prompt" aria-live="polite">
      {device === 'ios' && (
        <div className="biometric-prompt__face-id">
          <FaceIDIcon />
          <p>Look at your device</p>
        </div>
      )}

      {device === 'mac' && (
        <div className="biometric-prompt__touch-id">
          <TouchIDIcon />
          <p>Touch the sensor</p>
        </div>
      )}

      {device === 'windows' && (
        <div className="biometric-prompt__windows-hello">
          <WindowsHelloIcon />
          <p>Use Windows Hello</p>
        </div>
      )}

      {device === 'android' && (
        <div className="biometric-prompt__fingerprint">
          <FingerprintIcon />
          <p>Use your fingerprint</p>
        </div>
      )}
    </div>
  );
}
```

```css
/* Biometric prompt animations */
.biometric-prompt {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-4);
  padding: var(--space-8);
}

.biometric-prompt__face-id svg,
.biometric-prompt__fingerprint svg {
  width: 80px;
  height: 80px;
  animation: pulse-glow 1.5s ease-in-out infinite;
}

@keyframes pulse-glow {
  0%, 100% {
    opacity: 1;
    filter: drop-shadow(0 0 10px var(--accent));
  }
  50% {
    opacity: 0.7;
    filter: drop-shadow(0 0 20px var(--accent));
  }
}

@media (prefers-reduced-motion: reduce) {
  .biometric-prompt__face-id svg,
  .biometric-prompt__fingerprint svg {
    animation: none;
  }
}
```

---

## PASSKEY MANAGEMENT

```tsx
// PasskeyManager.tsx
interface Passkey {
  id: string;
  name: string;
  createdAt: Date;
  lastUsedAt: Date;
  deviceType: 'phone' | 'computer' | 'security-key';
  isCurrentDevice: boolean;
}

export function PasskeyManager({ passkeys }: { passkeys: Passkey[] }) {
  const [deleting, setDeleting] = useState<string | null>(null);

  const handleDelete = async (id: string) => {
    const confirmed = await swal.confirm(
      'Remove this passkey?',
      'You won\'t be able to sign in with this device unless you create a new passkey.'
    );
    
    if (!confirmed) return;

    setDeleting(id);
    await deletePasskey(id);
    setDeleting(null);
  };

  return (
    <div className="passkey-manager">
      <h3>Your passkeys</h3>
      <p className="text-muted">
        Manage passkeys for signing in to your account.
      </p>

      <ul className="passkey-list">
        {passkeys.map(passkey => (
          <li key={passkey.id} className="passkey-item">
            <div className="passkey-item__icon">
              {passkey.deviceType === 'phone' && '📱'}
              {passkey.deviceType === 'computer' && '💻'}
              {passkey.deviceType === 'security-key' && '🔑'}
            </div>
            
            <div className="passkey-item__info">
              <strong>
                {passkey.name}
                {passkey.isCurrentDevice && (
                  <span className="badge badge--accent">This device</span>
                )}
              </strong>
              <span className="text-muted">
                Created {formatDate(passkey.createdAt)}
                {' · '}
                Last used {formatRelative(passkey.lastUsedAt)}
              </span>
            </div>

            <button
              className="btn btn--icon btn--danger-ghost"
              onClick={() => handleDelete(passkey.id)}
              disabled={deleting === passkey.id}
              aria-label={`Remove ${passkey.name}`}
            >
              {deleting === passkey.id ? <Spinner /> : <TrashIcon />}
            </button>
          </li>
        ))}
      </ul>

      <button className="btn btn--secondary" onClick={registerNewPasskey}>
        + Add another passkey
      </button>
    </div>
  );
}
```

---

## CONDITIONAL UI (AUTOFILL PASSKEYS)

Chrome/Safari can show passkeys in autofill:

```tsx
// AutofillPasskeyInput.tsx
export function LoginForm() {
  const [username, setUsername] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    // Enable conditional mediation (autofill passkeys)
    const abortController = new AbortController();

    navigator.credentials.get({
      mediation: 'conditional',
      publicKey: {
        challenge: new Uint8Array(32), // Get from server in real impl
        rpId: window.location.hostname,
        userVerification: 'preferred',
      },
      signal: abortController.signal,
    }).then((credential) => {
      if (credential) {
        // User selected a passkey from autofill
        handlePasskeyLogin(credential as PublicKeyCredential);
      }
    }).catch(() => {
      // Conditional mediation was aborted or failed
    });

    return () => abortController.abort();
  }, []);

  return (
    <form className="login-form">
      <div className="field">
        <label htmlFor="username">Email or username</label>
        <input
          ref={inputRef}
          type="text"
          id="username"
          autoComplete="username webauthn"  // Key attribute!
          value={username}
          onChange={(e) => setUsername(e.target.value)}
        />
      </div>

      <div className="field">
        <label htmlFor="password">Password</label>
        <input
          type="password"
          id="password"
          autoComplete="current-password webauthn"
        />
      </div>

      <button type="submit" className="btn btn--primary">
        Sign in
      </button>
    </form>
  );
}
```

---

## FALLBACK FLOWS

When biometrics fail, provide clear alternatives:

```tsx
// AuthFallback.tsx
function AuthFallback({ 
  reason, 
  onRetryBiometric, 
  onUsePassword 
}: AuthFallbackProps) {
  const messages = {
    'not-allowed': 'Authentication was cancelled.',
    'timeout': 'The request timed out. Please try again.',
    'not-supported': 'Your device doesn\'t support this authentication method.',
    'no-credentials': 'No passkey found for this account on this device.',
  };

  return (
    <div className="auth-fallback">
      <div className="auth-fallback__icon">⚠️</div>
      
      <p className="auth-fallback__message">
        {messages[reason] || 'Something went wrong.'}
      </p>

      <div className="auth-fallback__options">
        <button 
          className="btn btn--primary"
          onClick={onRetryBiometric}
        >
          🔐 Try passkey again
        </button>
        
        <button 
          className="btn btn--secondary"
          onClick={onUsePassword}
        >
          Use password instead
        </button>

        <a href="/help/passkeys" className="auth-fallback__help">
          Having trouble? Get help
        </a>
      </div>
    </div>
  );
}
```

---

## PROGRESSIVE ENROLLMENT

Encourage passkey adoption without forcing:

```tsx
// ProgressivePasskeyEnrollment.tsx
function usePasskeyEnrollmentPrompt() {
  const [shouldShow, setShouldShow] = useState(false);
  const { hasPasskey, loginCount } = useUserAuth();

  useEffect(() => {
    // Show prompt after 3rd password login, if no passkey
    if (!hasPasskey && loginCount >= 3) {
      setShouldShow(true);
    }
  }, [hasPasskey, loginCount]);

  const dismiss = () => {
    localStorage.setItem('passkey-prompt-dismissed', Date.now().toString());
    setShouldShow(false);
  };

  return { shouldShow, dismiss };
}

// After successful password login
function PostLoginPasskeyPrompt() {
  const { shouldShow, dismiss } = usePasskeyEnrollmentPrompt();

  if (!shouldShow) return null;

  return (
    <div className="passkey-prompt-banner">
      <div className="passkey-prompt-banner__content">
        <strong>🔐 Try signing in faster</strong>
        <p>Set up a passkey to sign in with your fingerprint or face.</p>
      </div>
      
      <div className="passkey-prompt-banner__actions">
        <button className="btn btn--primary btn--sm">
          Set up passkey
        </button>
        <button 
          className="btn btn--ghost btn--sm"
          onClick={dismiss}
        >
          Not now
        </button>
      </div>
    </div>
  );
}
```

---

## CHECKLIST

```
□ WebAuthn support detection before showing UI
□ Clear explanation of passkey benefits
□ Platform-specific icons/language (Face ID, Windows Hello, etc.)
□ Fallback to password always available
□ Error messages are human-readable
□ Loading state while waiting for biometric
□ Passkey management UI (view, delete)
□ Conditional mediation for autofill
□ Progressive enrollment (don't force)
□ Works across browsers (Chrome, Safari, Firefox)
□ Security key support for enterprise
□ Cross-device authentication flow
□ Accessible to screen readers
```

---

## BROWSER SUPPORT

| Feature | Chrome | Safari | Firefox | Edge |
|---------|--------|--------|---------|------|
| WebAuthn | ✅ | ✅ | ✅ | ✅ |
| Platform Auth | ✅ | ✅ | ✅ | ✅ |
| Conditional UI | ✅ | ✅ | ❌ | ✅ |
| Cross-device | ✅ | ✅ | ❌ | ✅ |
| Security Keys | ✅ | ✅ | ✅ | ✅ |
