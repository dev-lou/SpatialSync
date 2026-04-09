---
description: Real-time features including WebSockets, Server-Sent Events (SSE), live dashboards, collaborative editing (CRDTs), and chat interfaces.
---

# UI/UX Real-time Features 2026

> WebSockets (Socket.io, Pusher, native WebSocket API), Server-Sent Events (EventSource), 
> live dashboards, collaborative editing (CRDTs), and real-time chat.

---

## 1. REAL-TIME DATA ARCHITECTURE

### WebSockets (Two-way Communication)
Best for: Chat, collaborative editing, real-time gaming.
**Tools**: Socket.io, Pusher, native `WebSocket` API.

### Server-Sent Events (One-way Streaming)
Best for: Live dashboards, stock tickers, notification feeds.
**Tools**: `EventSource` API.

### Free Tier Recommendations (2026)
- **Pusher**: 200k messages/day (Great for starters).
- **Supabase Realtime**: Included in free tier (Postgres-backed).
- **Ably**: Generous free tier with high reliability.

---

## 2. LIVE DASHBOARD (React + WebSocket)

```jsx
import React, { useEffect, useState } from 'react';
import { ResponsiveContainer, LineChart, Line, XAxis, YAxis, Tooltip } from 'recharts';

export function LiveChart() {
  const [data, setData] = useState([]);

  useEffect(() => {
    // Native WebSocket Example
    const ws = new WebSocket('wss://api.example.com/realtime');

    ws.onmessage = (event) => {
      const newData = JSON.parse(event.data);
      setData(prev => [...prev.slice(-19), newData]); // Keep last 20 points
    };

    return () => ws.close();
  }, []);

  return (
    <div className="kpi-card">
      <h3 className="kpi-card__title">Live Traffic</h3>
      <div style={{ height: 200, width: '100%' }}>
        <ResponsiveContainer width="100%" height="100%">
          <LineChart data={data}>
            <Line type="monotone" dataKey="value" stroke="var(--accent)" strokeWidth={2} dot={false} isAnimationActive={false} />
            <XAxis dataKey="time" hide />
            <YAxis hide domain={['auto', 'auto']} />
            <Tooltip />
          </LineChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
```

---

## 3. REAL-TIME CHAT (Minimal Socket.io Example)

```javascript
import { io } from "socket.io-client";

const socket = io("https://chat-server.example.com");

// Sending a message
function sendMessage(msg) {
  socket.emit("chat message", {
    text: msg,
    user: "User123",
    timestamp: new Date().toISOString()
  });
}

// Receiving a message
socket.on("chat message", (msg) => {
  displayMessage(msg); // Update UI
  swal.toast(`New message from ${msg.user}`, 'info');
});
```

---

## 4. COLLABORATIVE EDITING (CRDTs)

For multi-user collaboration (like Google Docs or Figma), use **CRDTs** (Conflict-free Replicated Data Types) to ensure all users see the same state without a central coordinator.

- **Yjs**: The leading CRDT library for shared state.
- **Automerge**: Powerful alternative for JSON-like data.
- **Hocuspocus**: Backend for Yjs (works with Node.js).

> **Simple Logic**: Instead of sending the whole document, send only the "delta" (changes). CRDTs merge these deltas automatically.

---

## 5. REAL-TIME UX BEST PRACTICES

- **Optimistic UI**: Update the UI immediately before the server confirms (law #13).
- **Connection Status**: Always show if the user is "Online" or "Disconnected".
- **Throttling**: Don't update the UI 100 times per second; batch updates to 60fps or less.
- **Empty States**: Show a skeleton or "Waiting for data..." if the stream hasn't started (law #10).

---

## 6. OFFLINE QUEUE WITH INDEXEDDB

**Rule**: When WebSocket is disconnected, queue outgoing messages in IndexedDB. On reconnect, send all queued messages. Show an offline banner using design tokens.

### Offline Banner Component

```html
<!-- Offline banner — shows when disconnected -->
<div class="offline-banner" id="offline-banner" role="alert" aria-live="polite" hidden>
  <div class="offline-banner__content">
    <i data-lucide="wifi-off"></i>
    <span>You're offline. Messages will be sent when you reconnect.</span>
  </div>
  <span class="offline-banner__queue" id="queue-count"></span>
</div>
```

```css
/* Token-styled offline banner */
.offline-banner {
  position: fixed;
  bottom: var(--space-6);
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  
  display: flex;
  align-items: center;
  gap: var(--space-4);
  
  padding: var(--space-3) var(--space-6);
  background: var(--color-warning-bg);
  border: 1px solid var(--color-warning-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  
  font-size: var(--text-sm);
  color: var(--color-warning);
}

.offline-banner[hidden] {
  display: none;
}

.offline-banner__content {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.offline-banner__content svg {
  width: var(--space-5);
  height: var(--space-5);
  flex-shrink: 0;
}

.offline-banner__queue {
  background: var(--color-warning);
  color: white;
  font-size: var(--text-xs);
  font-weight: 600;
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-full);
  min-width: 24px;
  text-align: center;
}

/* Animation for banner appearance */
@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

.offline-banner:not([hidden]) {
  animation: slide-up 0.3s var(--ease-out);
}

@media (prefers-reduced-motion: reduce) {
  .offline-banner:not([hidden]) {
    animation: none;
  }
}
```

### IndexedDB Offline Queue

```javascript
/**
 * Offline message queue using IndexedDB
 * Stores outgoing messages when WebSocket is disconnected
 * Sends all queued messages on reconnect
 */
class OfflineQueue {
  constructor(dbName = 'offlineQueue', storeName = 'messages') {
    this.dbName = dbName;
    this.storeName = storeName;
    this.db = null;
    this.isOnline = navigator.onLine;
    
    this.init();
  }

  async init() {
    // Check for IndexedDB support
    if (!('indexedDB' in window)) {
      console.warn('IndexedDB not supported. Falling back to memory queue.');
      this.memoryQueue = [];
      return;
    }

    try {
      this.db = await this.openDatabase();
      this.setupOnlineListener();
    } catch (err) {
      console.error('Failed to open IndexedDB:', err);
      this.memoryQueue = [];
    }
  }

  openDatabase() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(this.dbName, 1);

      request.onerror = () => reject(request.error);
      request.onsuccess = () => resolve(request.result);

      request.onupgradeneeded = (event) => {
        const db = event.target.result;
        if (!db.objectStoreNames.contains(this.storeName)) {
          const store = db.createObjectStore(this.storeName, {
            keyPath: 'id',
            autoIncrement: true,
          });
          store.createIndex('timestamp', 'timestamp', { unique: false });
        }
      };
    });
  }

  setupOnlineListener() {
    window.addEventListener('online', () => {
      this.isOnline = true;
      this.hideOfflineBanner();
      this.onReconnect?.();
    });

    window.addEventListener('offline', () => {
      this.isOnline = false;
      this.showOfflineBanner();
    });
  }

  async enqueue(message) {
    const entry = {
      message,
      timestamp: Date.now(),
      attempts: 0,
    };

    if (this.db) {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction(this.storeName, 'readwrite');
        const store = tx.objectStore(this.storeName);
        const request = store.add(entry);
        
        request.onsuccess = () => {
          this.updateQueueCount();
          resolve(request.result);
        };
        request.onerror = () => reject(request.error);
      });
    } else if (this.memoryQueue) {
      // Fallback to memory
      this.memoryQueue.push(entry);
      this.updateQueueCount();
      return Promise.resolve(this.memoryQueue.length);
    }
  }

  async dequeue() {
    if (this.db) {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction(this.storeName, 'readwrite');
        const store = tx.objectStore(this.storeName);
        const index = store.index('timestamp');
        const request = index.openCursor();

        request.onsuccess = (event) => {
          const cursor = event.target.result;
          if (cursor) {
            const entry = cursor.value;
            cursor.delete();
            this.updateQueueCount();
            resolve(entry);
          } else {
            resolve(null);
          }
        };
        request.onerror = () => reject(request.error);
      });
    } else if (this.memoryQueue) {
      const entry = this.memoryQueue.shift();
      this.updateQueueCount();
      return Promise.resolve(entry || null);
    }
    return null;
  }

  async getAll() {
    if (this.db) {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction(this.storeName, 'readonly');
        const store = tx.objectStore(this.storeName);
        const request = store.getAll();
        
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
      });
    } else if (this.memoryQueue) {
      return Promise.resolve([...this.memoryQueue]);
    }
    return [];
  }

  async clear() {
    if (this.db) {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction(this.storeName, 'readwrite');
        const store = tx.objectStore(this.storeName);
        const request = store.clear();
        
        request.onsuccess = () => {
          this.updateQueueCount();
          resolve();
        };
        request.onerror = () => reject(request.error);
      });
    } else if (this.memoryQueue) {
      this.memoryQueue = [];
      this.updateQueueCount();
      return Promise.resolve();
    }
  }

  async getCount() {
    if (this.db) {
      return new Promise((resolve, reject) => {
        const tx = this.db.transaction(this.storeName, 'readonly');
        const store = tx.objectStore(this.storeName);
        const request = store.count();
        
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
      });
    } else if (this.memoryQueue) {
      return Promise.resolve(this.memoryQueue.length);
    }
    return 0;
  }

  async updateQueueCount() {
    const count = await this.getCount();
    const countEl = document.getElementById('queue-count');
    if (countEl) {
      countEl.textContent = count > 0 ? `${count} pending` : '';
      countEl.hidden = count === 0;
    }
  }

  showOfflineBanner() {
    const banner = document.getElementById('offline-banner');
    if (banner) {
      banner.hidden = false;
      this.updateQueueCount();
    }
  }

  hideOfflineBanner() {
    const banner = document.getElementById('offline-banner');
    if (banner) {
      banner.hidden = true;
    }
  }
}
```

### WebSocket with Offline Queue Integration

```javascript
class RealtimeConnection {
  constructor(url) {
    this.url = url;
    this.ws = null;
    this.queue = new OfflineQueue();
    this.reconnectAttempts = 0;
    this.maxReconnectDelay = 30000;
    
    // Set up reconnect handler
    this.queue.onReconnect = () => this.flushQueue();
    
    this.connect();
  }

  connect() {
    this.ws = new WebSocket(this.url);

    this.ws.onopen = () => {
      console.log('WebSocket connected');
      this.reconnectAttempts = 0;
      this.queue.hideOfflineBanner();
      this.flushQueue();
    };

    this.ws.onclose = () => {
      console.log('WebSocket disconnected');
      this.queue.showOfflineBanner();
      this.scheduleReconnect();
    };

    this.ws.onerror = (error) => {
      console.error('WebSocket error:', error);
    };

    this.ws.onmessage = (event) => {
      this.handleMessage(JSON.parse(event.data));
    };
  }

  scheduleReconnect() {
    const delay = Math.min(
      1000 * Math.pow(2, this.reconnectAttempts),
      this.maxReconnectDelay
    );
    this.reconnectAttempts++;
    
    setTimeout(() => {
      if (navigator.onLine) {
        this.connect();
      }
    }, delay);
  }

  async send(message) {
    if (this.ws?.readyState === WebSocket.OPEN) {
      this.ws.send(JSON.stringify(message));
    } else {
      // Queue message for later
      await this.queue.enqueue(message);
      swal.toast('Message queued — will send when online', 'info');
    }
  }

  async flushQueue() {
    let entry;
    while ((entry = await this.queue.dequeue())) {
      if (this.ws?.readyState === WebSocket.OPEN) {
        this.ws.send(JSON.stringify(entry.message));
      } else {
        // Put it back if we disconnected
        await this.queue.enqueue(entry.message);
        break;
      }
    }
  }

  handleMessage(data) {
    // Handle incoming messages
    console.log('Received:', data);
  }
}

// Usage
const realtime = new RealtimeConnection('wss://api.example.com/ws');

// Send message (automatically queued if offline)
document.getElementById('send-btn').addEventListener('click', () => {
  realtime.send({
    type: 'chat',
    text: document.getElementById('message-input').value,
    timestamp: Date.now(),
  });
});
```

### Browser Support Fallback

```javascript
// Check for IndexedDB support and provide fallback
function supportsIndexedDB() {
  try {
    return 'indexedDB' in window && indexedDB !== null;
  } catch (e) {
    return false;
  }
}

// For browsers without IndexedDB (rare in 2026), use localStorage
class LocalStorageQueue {
  constructor(key = 'offlineQueue') {
    this.key = key;
  }

  getQueue() {
    try {
      return JSON.parse(localStorage.getItem(this.key) || '[]');
    } catch {
      return [];
    }
  }

  saveQueue(queue) {
    try {
      localStorage.setItem(this.key, JSON.stringify(queue));
    } catch (e) {
      console.error('localStorage full or unavailable:', e);
    }
  }

  async enqueue(message) {
    const queue = this.getQueue();
    queue.push({ message, timestamp: Date.now() });
    this.saveQueue(queue);
  }

  async dequeue() {
    const queue = this.getQueue();
    const entry = queue.shift();
    this.saveQueue(queue);
    return entry || null;
  }

  async getCount() {
    return this.getQueue().length;
  }

  async clear() {
    this.saveQueue([]);
  }
}

// Use appropriate queue based on support
const QueueClass = supportsIndexedDB() ? OfflineQueue : LocalStorageQueue;
const queue = new QueueClass();
```
