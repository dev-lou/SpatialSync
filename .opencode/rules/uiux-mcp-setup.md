---
description: Concrete Model Context Protocol (MCP) server configurations for Chrome DevTools and Lighthouse CLI.
---

# OpenCode UI/UX 2026 — MCP Server Setup

This document provides concrete configuration snippets for setting up Model Context Protocol (MCP) servers for Chrome DevTools and Lighthouse CLI. These configurations are essential for automating UI/UX audits and performance assessments within the OpenCode design system. Both tools are **free and open source**, ensuring broad accessibility and cost-effectiveness.

## 1. Chrome DevTools MCP Configuration

This configuration enables the `uiux-audit-automation.md` skill to interact with Chrome DevTools for automated accessibility (axe-core) scans and other UI/UX audits. The MCP server facilitates programmatic control over a headless Chrome instance, allowing for consistent and repeatable testing.

### `opencode.json` Snippet

Add the following object to the `servers` array within your `opencode.json` file. Ensure the `port` is unique if you have other MCP servers running.

```json
{
  "name": "chrome-devtools-mcp",
  "type": "devtools",
  "port": 9222, // Default Chrome DevTools port
  "args": [
    "--headless=new",
    "--disable-gpu",
    "--remote-debugging-port=9222",
    "--no-sandbox" // Required for some environments, use with caution
  ],
  "chromePath": "/usr/bin/google-chrome" // Adjust based on your Chrome installation path
}
```

### Verification Steps

After adding the configuration and restarting your MCP client, you can verify the server is running and accessible using the `/mcp list` command:

```bash
/mcp list
```

You should see `chrome-devtools-mcp` listed among the active servers. You can also try to connect to `localhost:9222` in your browser to confirm the headless Chrome instance is listening.

### Troubleshooting Tips

*   **Chrome Path**: Ensure `chromePath` points to the correct executable for your system. Common paths include `/usr/bin/google-chrome`, `/usr/bin/chromium-browser`, or `/Applications/Google Chrome.app/Contents/MacOS/Google Chrome` on macOS.
*   **Permissions**: If you encounter permission errors, ensure the user running the MCP server has execute permissions for the Chrome executable. The `--no-sandbox` flag can resolve some issues in containerized environments but should be used judiciously due to security implications.
*   **Port Conflicts**: If port `9222` is already in use, choose another available port and update the `port` and `remote-debugging-port` values accordingly.

## 2. Lighthouse CLI MCP Configuration

This configuration integrates Lighthouse CLI into the MCP ecosystem, allowing the `performance-auditor.md` skill to trigger performance, accessibility, best practices, SEO, and PWA audits programmatically. This is crucial for pre-ship performance checks and continuous integration.

### `opencode.json` Snippet

Add the following object to the `servers` array within your `opencode.json` file. This configuration typically leverages the Chrome DevTools MCP for its underlying browser control.

```json
{
  "name": "lighthouse-cli-mcp",
  "type": "cli",
  "command": "lighthouse",
  "args": [
    "--output=json",
    "--output-path=stdout",
    "--port=9222", // Connects to the Chrome DevTools MCP instance
    "--chrome-flags=\"--headless=new --disable-gpu --no-sandbox\""
  ],
  "env": {
    "PATH": "/usr/local/bin:/usr/bin" // Ensure lighthouse is in PATH
  }
}
```

### Verification Steps

Similar to the Chrome DevTools MCP, verify the Lighthouse CLI server is recognized by your MCP client:

```bash
/mcp list
```

`lighthouse-cli-mcp` should appear in the list. You can then attempt to run a basic audit through the MCP client to confirm functionality.

### Troubleshooting Tips

*   **Lighthouse Installation**: Ensure Lighthouse CLI is installed globally (`npm install -g lighthouse`). Verify its presence by running `lighthouse --version` in your terminal.
*   **PATH Environment Variable**: The `env` section in the `opencode.json` snippet ensures that the `lighthouse` command can be found. Adjust the `PATH` if your `lighthouse` executable is located elsewhere.
*   **Chrome DevTools Dependency**: The Lighthouse CLI MCP relies on a running Chrome DevTools instance. Ensure your `chrome-devtools-mcp` is correctly configured and active on the specified port (`9222` in this example).
*   **`--no-sandbox` Flag**: As with Chrome DevTools, the `--no-sandbox` flag might be necessary in certain environments. Understand its implications before using it in production. If you are running in a Docker container, this flag is often required.
*   **MCP Client Configuration**: Ensure your MCP client is correctly configured to read the `opencode.json` file and start the defined servers. Refer to your MCP client's documentation for details on loading configuration files.
