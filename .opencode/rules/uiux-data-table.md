---
description: Advanced data table patterns including TanStack Table, sortable, filterable, paginated, row selection, virtualization, filter sidebars, bulk actions, activity logs, and kanban boards.
---

# UI/UX Data Table & Kanban 2026

> Data tables (TanStack Table), sortable, filterable, paginated, 
> row selection, virtualization, filter sidebars, bulk actions, 
> activity logs, and kanban boards.

---

## 1. DATA TABLE (TanStack Table Headless)

```jsx
// React Example with TanStack Table
import { useReactTable, getCoreRowModel, flexRender } from '@tanstack/react-table';

function DataTable({ data, columns }) {
  const table = useReactTable({ data, columns, getCoreRowModel: getCoreRowModel() });

  return (
    <div class="table-container">
      <table class="table">
        <thead>
          {table.getHeaderGroups().map(headerGroup => (
            <tr key={headerGroup.id}>
              {headerGroup.headers.map(header => (
                <th key={header.id} class="table__th">
                  {flexRender(header.column.columnDef.header, header.getContext())}
                </th>
              ))}
            </tr>
          ))}
        </thead>
        <tbody>
          {table.getRowModel().rows.map(row => (
            <tr key={row.id} class="table__tr">
              {row.getVisibleCells().map(cell => (
                <td key={cell.id} class="table__td">
                  {flexRender(cell.column.columnDef.cell, cell.getContext())}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
```

```css
.table-container {
  width: 100%;
  overflow-x: auto;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  background: var(--surface);
}

.table {
  width: 100%;
  border-collapse: collapse;
  font-size: var(--text-sm);
}

.table__th {
  text-align: left;
  padding: var(--space-4);
  background: var(--surface-up);
  color: var(--muted);
  font-weight: 600;
  border-bottom: 1px solid var(--border);
}

.table__tr {
  border-bottom: 1px solid var(--border);
  transition: background var(--dur-micro) var(--ease-out);
}

.table__tr:hover { background: var(--surface-up); }

.table__td { padding: var(--space-4); color: var(--text); }
```

---

## 2. FILTER SIDEBAR (Multi-Faceted)

```html
<aside class="filter-sidebar">
  <div class="filter-group">
    <h4 class="filter-group__title">Status</h4>
    <label class="checkbox-label"><input type="checkbox"> Active</label>
    <label class="checkbox-label"><input type="checkbox"> Pending</label>
  </div>
  <div class="filter-group">
    <h4 class="filter-group__title">Date Range</h4>
    <input type="date" class="field__input">
  </div>
</aside>
```

```css
.filter-sidebar {
  width: 240px;
  padding: var(--space-6);
  background: var(--surface);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.filter-group__title {
  font-size: var(--text-xs);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--muted);
  margin-bottom: var(--space-3);
}
```

---

## 3. BULK ACTIONS (Selection & Export)

```html
<div class="bulk-actions" hidden>
  <span class="bulk-actions__count">3 rows selected</span>
  <div class="bulk-actions__btns">
    <button class="btn btn--sm btn--outline"><i data-lucide="trash"></i> Delete</button>
    <button class="btn btn--sm btn--outline"><i data-lucide="download"></i> Export</button>
  </div>
</div>
```

```css
.bulk-actions {
  position: sticky;
  bottom: var(--space-6);
  left: 50%;
  transform: translateX(-50%);
  background: var(--text);
  color: var(--bg);
  padding: var(--space-3) var(--space-6);
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  gap: var(--space-6);
  box-shadow: var(--shadow-xl);
  z-index: 100;
}
```

---

## 4. ACTIVITY LOG / AUDIT TRAIL

```html
<ul class="activity-log">
  <li class="activity-log__item">
    <div class="activity-log__dot"></div>
    <div class="activity-log__content">
      <p><strong>John Doe</strong> updated the project status to <span class="badge badge--success">Active</span></p>
      <time class="activity-log__time">2 hours ago</time>
    </div>
  </li>
</ul>
```

```css
.activity-log {
  list-style: none;
  padding-left: var(--space-4);
  border-left: 2px solid var(--border);
}

.activity-log__item {
  position: relative;
  padding-bottom: var(--space-6);
  padding-left: var(--space-6);
}

.activity-log__dot {
  position: absolute;
  left: -25px; top: 4px;
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--accent);
  border: 2px solid var(--bg);
}

.activity-log__time { font-size: var(--text-xs); color: var(--muted); }
```

---

## 5. KANBAN BOARD (Drag-and-Drop)

```html
<div class="kanban">
  <div class="kanban__column">
    <h3 class="kanban__column-title">To Do</h3>
    <div class="kanban__list" id="todo-list">
      <div class="kanban__card" draggable="true">
        <h4>Task 1</h4>
        <p>Description...</p>
      </div>
    </div>
  </div>
</div>
```

```css
.kanban {
  display: flex;
  gap: var(--space-6);
  overflow-x: auto;
  padding-bottom: var(--space-6);
}

.kanban__column {
  flex: 0 0 320px;
  background: var(--surface-up);
  border-radius: var(--radius-lg);
  padding: var(--space-4);
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.kanban__card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: var(--space-4);
  box-shadow: var(--shadow-sm);
  cursor: grab;
}

.kanban__card:active { cursor: grabbing; }
```
