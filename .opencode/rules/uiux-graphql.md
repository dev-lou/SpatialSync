---
description: GraphQL API integration including Apollo Client, URQL, code generation, caching strategies, and 2026 GraphQL-first APIs.
---

# UI/UX GraphQL APIs 2026

> Apollo Client (React/Next.js), URQL (lightweight alternative), 
> code generation (GraphQL Code Generator), and caching strategies.

---

## 1. GRAPHQL ARCHITECTURE (2026)

GraphQL is the industry standard for 2026 APIs like **GitHub**, **Shopify**, and **Contentful**.

### Apollo Client (Enterprise Choice)
Best for: Large React/Next.js apps with complex state and caching needs.
**Features**: Normalized caching, local state management, built-in loading/error states.

### URQL (Lightweight Alternative)
Best for: Small to medium apps where bundle size matters.
**Features**: Simple API, modular "exchanges", highly performant.

### GraphQL Code Generator (Mandatory)
**Rule**: Never manually type your GraphQL queries or responses. Use `graphql-codegen` to generate TypeScript types from your schema and queries.

---

## 2. APOLLO CLIENT SETUP (React/Next.js)

```jsx
import { ApolloClient, InMemoryCache, ApolloProvider } from '@apollo/client';

const client = new ApolloClient({
  uri: 'https://api.shopify.com/api/2024-04/graphql.json',
  cache: new InMemoryCache(),
  headers: {
    'X-Shopify-Storefront-Access-Token': process.env.NEXT_PUBLIC_SHOPIFY_TOKEN,
  },
});

export function AppWrapper({ children }) {
  return <ApolloProvider client={client}>{children}</ApolloProvider>;
}
```

---

## 3. DATA TABLE (Shopify Products Example)

```jsx
import { useQuery, gql } from '@apollo/client';

const GET_PRODUCTS = gql`
  query GetProducts($first: Int!) {
    products(first: $first) {
      edges {
        node {
          id
          title
          handle
          priceRange {
            minVariantPrice {
              amount
              currencyCode
            }
          }
        }
      }
    }
  }
`;

export function ProductTable() {
  const { loading, error, data } = useQuery(GET_PRODUCTS, {
    variables: { first: 10 },
  });

  if (loading) return <div className="skeleton" style={{ height: 400 }}></div>;
  if (error) return <div className="error-state">Error: {error.message}</div>;

  return (
    <div className="data-table-container">
      <table className="data-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          {data.products.edges.map(({ node }) => (
            <tr key={node.id} data-id={node.id}>
              <td>{node.title}</td>
              <td>{node.priceRange.minVariantPrice.amount} {node.priceRange.minVariantPrice.currencyCode}</td>
              <td><span className="badge badge--success">Active</span></td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
```

---

## 4. CACHING STRATEGIES

- **Normalized Caching**: Apollo stores objects by `__typename` and `id`. Updating a product in one component updates it everywhere automatically.
- **Partial Data**: Use `@client` fields for local-only data (e.g., `isFavorited`) without hitting the server.
- **Refetching vs Polling**: Use `refetch()` for manual updates or `pollInterval` for near real-time updates without WebSockets.

---

## 5. GRAPHQL UX BEST PRACTICES

- **Fragment Colocation**: Define GraphQL fragments inside the component that uses them.
- **Error Handling**: Use `errorPolicy: 'all'` to show partial data even if some fields fail.
- **Loading States**: Use `useQuery`'s `loading` boolean to show skeletons (law #10).
- **Optimistic UI**: Use `optimisticResponse` in mutations for instant feedback (law #13).
