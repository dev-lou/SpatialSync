---
description: Typo-tolerant search using Fuse.js or lunr.js. Includes debounced input, match highlighting, "did you mean?" suggestions, and empty state handling. Token-aware styling.
---

# UI/UX Fuzzy Search

> Implement typo-tolerant search with match highlighting and suggestions.
> Uses Fuse.js for client-side fuzzy matching with token-aware styling.

---

## OVERVIEW

This skill covers:
1. Fuse.js/lunr.js setup and configuration
2. Debounced input handling
3. Match highlighting
4. "Did you mean?" suggestions
5. Empty state and no results handling
6. Token-aware styling

---

## INSTALLATION

```bash
# Fuse.js (recommended for most use cases)
npm install fuse.js

# lunr.js (for larger datasets with full-text search)
npm install lunr
```

---

## FUSE.JS IMPLEMENTATION

### Basic Setup

```typescript
import Fuse from 'fuse.js';

interface SearchItem {
  id: string;
  title: string;
  description: string;
  category: string;
  tags: string[];
}

const fuseOptions: Fuse.IFuseOptions<SearchItem> = {
  // Which keys to search
  keys: [
    { name: 'title', weight: 2 },      // Higher weight = more important
    { name: 'description', weight: 1 },
    { name: 'category', weight: 1.5 },
    { name: 'tags', weight: 1 }
  ],
  
  // Fuzzy matching settings
  threshold: 0.4,          // 0 = exact match, 1 = match anything
  distance: 100,           // How far to search for a match
  minMatchCharLength: 2,   // Minimum characters before searching
  
  // Include match info for highlighting
  includeScore: true,
  includeMatches: true,
  
  // Performance
  ignoreLocation: true,    // Search entire string, not just beginning
  useExtendedSearch: true, // Enable advanced search patterns
};

// Create the search index
const fuse = new Fuse(items, fuseOptions);

// Search
const results = fuse.search('serch query'); // Typo-tolerant!
```

### Search Component

```tsx
import { useState, useMemo, useCallback } from 'react';
import Fuse from 'fuse.js';
import { Search, X, Loader2 } from 'lucide-react';
import { useDebounce } from '@/hooks/useDebounce';

interface FuzzySearchProps<T> {
  items: T[];
  keys: Fuse.FuseOptionKey<T>[];
  placeholder?: string;
  onSelect?: (item: T) => void;
  renderItem?: (item: T, matches: Fuse.FuseResultMatch[]) => React.ReactNode;
  emptyMessage?: string;
  threshold?: number;
}

export function FuzzySearch<T extends { id: string }>({
  items,
  keys,
  placeholder = 'Search...',
  onSelect,
  renderItem,
  emptyMessage = 'No results found',
  threshold = 0.4
}: FuzzySearchProps<T>) {
  const [query, setQuery] = useState('');
  const [isOpen, setIsOpen] = useState(false);
  const [selectedIndex, setSelectedIndex] = useState(0);
  
  const debouncedQuery = useDebounce(query, 200);
  
  // Create Fuse instance
  const fuse = useMemo(() => {
    return new Fuse(items, {
      keys,
      threshold,
      includeMatches: true,
      includeScore: true,
      minMatchCharLength: 2,
    });
  }, [items, keys, threshold]);
  
  // Get search results
  const results = useMemo(() => {
    if (!debouncedQuery.trim()) return [];
    return fuse.search(debouncedQuery).slice(0, 10);
  }, [fuse, debouncedQuery]);
  
  // Handle keyboard navigation
  const handleKeyDown = useCallback((e: React.KeyboardEvent) => {
    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        setSelectedIndex(i => Math.min(i + 1, results.length - 1));
        break;
      case 'ArrowUp':
        e.preventDefault();
        setSelectedIndex(i => Math.max(i - 1, 0));
        break;
      case 'Enter':
        e.preventDefault();
        if (results[selectedIndex]) {
          onSelect?.(results[selectedIndex].item);
          setQuery('');
          setIsOpen(false);
        }
        break;
      case 'Escape':
        setIsOpen(false);
        break;
    }
  }, [results, selectedIndex, onSelect]);
  
  return (
    <div className="fuzzy-search" role="combobox" aria-expanded={isOpen}>
      <div className="fuzzy-search__input-wrap">
        <Search className="fuzzy-search__icon" aria-hidden="true" />
        <input
          type="text"
          className="fuzzy-search__input"
          value={query}
          onChange={(e) => {
            setQuery(e.target.value);
            setIsOpen(true);
            setSelectedIndex(0);
          }}
          onFocus={() => setIsOpen(true)}
          onBlur={() => setTimeout(() => setIsOpen(false), 200)}
          onKeyDown={handleKeyDown}
          placeholder={placeholder}
          aria-label="Search"
          aria-autocomplete="list"
          aria-controls="search-results"
        />
        {query && (
          <button 
            className="fuzzy-search__clear"
            onClick={() => setQuery('')}
            aria-label="Clear search"
          >
            <X size={16} />
          </button>
        )}
      </div>
      
      {isOpen && query.length >= 2 && (
        <ul 
          id="search-results"
          className="fuzzy-search__results"
          role="listbox"
        >
          {results.length > 0 ? (
            results.map((result, index) => (
              <li
                key={result.item.id}
                className={`fuzzy-search__result ${
                  index === selectedIndex ? 'fuzzy-search__result--selected' : ''
                }`}
                role="option"
                aria-selected={index === selectedIndex}
                onClick={() => {
                  onSelect?.(result.item);
                  setQuery('');
                  setIsOpen(false);
                }}
              >
                {renderItem ? (
                  renderItem(result.item, result.matches || [])
                ) : (
                  <HighlightedText 
                    text={(result.item as any).title || ''} 
                    matches={result.matches?.find(m => m.key === 'title')?.indices || []}
                  />
                )}
                {result.score !== undefined && (
                  <span className="fuzzy-search__score">
                    {Math.round((1 - result.score) * 100)}% match
                  </span>
                )}
              </li>
            ))
          ) : (
            <li className="fuzzy-search__empty">
              {emptyMessage}
              <DidYouMean query={query} items={items} keys={keys} />
            </li>
          )}
        </ul>
      )}
    </div>
  );
}
```

### Match Highlighting

```tsx
interface HighlightedTextProps {
  text: string;
  matches: readonly [number, number][];
}

export function HighlightedText({ text, matches }: HighlightedTextProps) {
  if (!matches.length) return <span>{text}</span>;
  
  const parts: React.ReactNode[] = [];
  let lastIndex = 0;
  
  // Sort matches by start index
  const sortedMatches = [...matches].sort((a, b) => a[0] - b[0]);
  
  sortedMatches.forEach(([start, end], i) => {
    // Add non-highlighted text before this match
    if (start > lastIndex) {
      parts.push(
        <span key={`text-${i}`}>{text.slice(lastIndex, start)}</span>
      );
    }
    
    // Add highlighted match
    parts.push(
      <mark key={`match-${i}`} className="fuzzy-search__highlight">
        {text.slice(start, end + 1)}
      </mark>
    );
    
    lastIndex = end + 1;
  });
  
  // Add remaining text
  if (lastIndex < text.length) {
    parts.push(
      <span key="text-end">{text.slice(lastIndex)}</span>
    );
  }
  
  return <>{parts}</>;
}
```

### "Did You Mean?" Suggestions

```tsx
interface DidYouMeanProps<T> {
  query: string;
  items: T[];
  keys: Fuse.FuseOptionKey<T>[];
}

function DidYouMean<T>({ query, items, keys }: DidYouMeanProps<T>) {
  // Search with very high threshold to find close matches
  const fuse = useMemo(() => {
    return new Fuse(items, {
      keys,
      threshold: 0.6, // More lenient
      includeScore: true,
    });
  }, [items, keys]);
  
  const suggestions = useMemo(() => {
    if (!query) return [];
    const results = fuse.search(query);
    // Only suggest if score is reasonable but not too good
    return results
      .filter(r => r.score && r.score > 0.3 && r.score < 0.6)
      .slice(0, 3);
  }, [fuse, query]);
  
  if (!suggestions.length) return null;
  
  return (
    <div className="fuzzy-search__suggestions">
      <span>Did you mean: </span>
      {suggestions.map((s, i) => (
        <button 
          key={i}
          className="fuzzy-search__suggestion"
          onClick={() => {
            // Trigger search with suggestion
          }}
        >
          {(s.item as any).title}
        </button>
      ))}
    </div>
  );
}
```

---

## DEBOUNCE HOOK

```typescript
import { useState, useEffect } from 'react';

export function useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState(value);
  
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);
    
    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);
  
  return debouncedValue;
}
```

---

## TOKEN-AWARE STYLES

```css
.fuzzy-search {
  position: relative;
  width: 100%;
  max-width: 480px;
}

.fuzzy-search__input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.fuzzy-search__icon {
  position: absolute;
  left: var(--space-3);
  width: var(--space-5);
  height: var(--space-5);
  color: var(--muted);
  pointer-events: none;
}

.fuzzy-search__input {
  width: 100%;
  height: 44px;
  padding: var(--space-3) var(--space-10);
  padding-right: var(--space-10);
  font-size: var(--text-base);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  color: var(--text);
  transition: border-color var(--dur-micro), box-shadow var(--dur-micro);
}

.fuzzy-search__input:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-soft);
}

.fuzzy-search__input::placeholder {
  color: var(--muted);
}

.fuzzy-search__clear {
  position: absolute;
  right: var(--space-3);
  display: flex;
  align-items: center;
  justify-content: center;
  width: var(--space-6);
  height: var(--space-6);
  background: none;
  border: none;
  color: var(--muted);
  cursor: pointer;
  border-radius: var(--radius-sm);
  transition: background var(--dur-micro), color var(--dur-micro);
}

.fuzzy-search__clear:hover {
  background: var(--bg-hover);
  color: var(--text);
}

/* Results dropdown */
.fuzzy-search__results {
  position: absolute;
  top: calc(100% + var(--space-2));
  left: 0;
  right: 0;
  max-height: 400px;
  overflow-y: auto;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  z-index: 100;
  list-style: none;
  padding: var(--space-2);
  margin: 0;
}

.fuzzy-search__result {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-3) var(--space-4);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: background var(--dur-micro);
}

.fuzzy-search__result:hover,
.fuzzy-search__result--selected {
  background: var(--bg-hover);
}

.fuzzy-search__result--selected {
  outline: 2px solid var(--accent);
  outline-offset: -2px;
}

/* Highlighting */
.fuzzy-search__highlight {
  background: var(--accent-soft);
  color: var(--accent);
  padding: 0 2px;
  border-radius: var(--radius-sm);
}

/* Score badge */
.fuzzy-search__score {
  font-size: var(--text-xs);
  color: var(--muted);
  padding: var(--space-1) var(--space-2);
  background: var(--bg);
  border-radius: var(--radius-sm);
}

/* Empty state */
.fuzzy-search__empty {
  padding: var(--space-8) var(--space-4);
  text-align: center;
  color: var(--muted);
}

/* Did you mean */
.fuzzy-search__suggestions {
  margin-top: var(--space-4);
  font-size: var(--text-sm);
}

.fuzzy-search__suggestion {
  background: none;
  border: none;
  color: var(--accent);
  text-decoration: underline;
  cursor: pointer;
  padding: 0;
  margin-left: var(--space-2);
}

.fuzzy-search__suggestion:hover {
  text-decoration: none;
}

/* Loading state */
.fuzzy-search__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-4);
  color: var(--muted);
}

.fuzzy-search__loading svg {
  animation: spin 1s linear infinite;
  margin-right: var(--space-2);
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Dark mode */
[data-theme="dark"] .fuzzy-search__highlight {
  background: var(--accent-soft);
}
```

---

## LUNR.JS FOR LARGER DATASETS

```typescript
import lunr from 'lunr';

interface Document {
  id: string;
  title: string;
  body: string;
  category: string;
}

// Build the index
const idx = lunr(function() {
  this.ref('id');
  this.field('title', { boost: 10 });
  this.field('body');
  this.field('category', { boost: 5 });
  
  documents.forEach((doc) => {
    this.add(doc);
  });
});

// Search
const results = idx.search('query');

// With fuzzy matching
const fuzzyResults = idx.search('query~1'); // Edit distance of 1

// With wildcards
const wildcardResults = idx.search('que*');

// Boost specific fields
const boostedResults = idx.search('title:query^10 body:query');
```

---

## EMPTY STATE COMPONENT

```tsx
import { Search } from 'lucide-react';

interface SearchEmptyStateProps {
  query: string;
  suggestions?: string[];
  onSuggestionClick?: (suggestion: string) => void;
}

export function SearchEmptyState({ 
  query, 
  suggestions,
  onSuggestionClick 
}: SearchEmptyStateProps) {
  return (
    <div className="search-empty-state">
      <div className="search-empty-state__icon">
        <Search size={48} />
      </div>
      <h3 className="search-empty-state__title">
        No results for "{query}"
      </h3>
      <p className="search-empty-state__message">
        Try checking your spelling or using different keywords.
      </p>
      
      {suggestions && suggestions.length > 0 && (
        <div className="search-empty-state__suggestions">
          <p>Try searching for:</p>
          <ul>
            {suggestions.map((suggestion, i) => (
              <li key={i}>
                <button onClick={() => onSuggestionClick?.(suggestion)}>
                  {suggestion}
                </button>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}
```

```css
.search-empty-state {
  text-align: center;
  padding: var(--space-12) var(--space-6);
}

.search-empty-state__icon {
  color: var(--muted);
  margin-bottom: var(--space-4);
}

.search-empty-state__title {
  font-size: var(--text-lg);
  font-weight: 600;
  color: var(--text);
  margin-bottom: var(--space-2);
}

.search-empty-state__message {
  color: var(--muted);
  margin-bottom: var(--space-6);
}

.search-empty-state__suggestions {
  text-align: center;
}

.search-empty-state__suggestions ul {
  list-style: none;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: var(--space-2);
  margin-top: var(--space-3);
}

.search-empty-state__suggestions button {
  padding: var(--space-2) var(--space-4);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-full);
  color: var(--accent);
  cursor: pointer;
  transition: background var(--dur-micro), border-color var(--dur-micro);
}

.search-empty-state__suggestions button:hover {
  background: var(--accent-soft);
  border-color: var(--accent);
}
```

---

## ACCESSIBILITY

```tsx
// ARIA attributes for search
<div 
  className="fuzzy-search" 
  role="combobox" 
  aria-expanded={isOpen}
  aria-haspopup="listbox"
  aria-owns="search-results"
>
  <input
    role="searchbox"
    aria-label="Search"
    aria-autocomplete="list"
    aria-controls="search-results"
    aria-activedescendant={selectedId}
  />
  
  <ul 
    id="search-results" 
    role="listbox"
    aria-label="Search results"
  >
    <li 
      role="option" 
      id={`result-${item.id}`}
      aria-selected={isSelected}
    >
      ...
    </li>
  </ul>
</div>

// Announce results to screen readers
<div 
  role="status" 
  aria-live="polite" 
  className="sr-only"
>
  {results.length} results found for "{query}"
</div>
```

---

## CHECKLIST

- [ ] Debounced input (200-300ms)
- [ ] Fuse.js configured with appropriate threshold
- [ ] Match highlighting implemented
- [ ] Keyboard navigation (up/down/enter/escape)
- [ ] "Did you mean?" for no results
- [ ] Empty state with suggestions
- [ ] Loading state
- [ ] ARIA attributes for accessibility
- [ ] Token-aware styling
- [ ] Mobile-friendly (touch targets ≥44px)
- [ ] Screen reader announcements
