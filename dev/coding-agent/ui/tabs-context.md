# UI Tabs Component - Context Instructions

## Overview
This document provides context for implementing tabs UI components based on the AppStack Bootstrap 5 template (ui-tabs.html).

## Tab Structure

### Basic Tab HTML Pattern
```html
<div class="tab">
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab">Tab Label</a>
    </li>
    <!-- Additional tab items -->
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="tab-1" role="tabpanel">
      <!-- Tab content here -->
    </div>
    <!-- Additional tab panes -->
  </div>
</div>
```

## Tab Variants

### 1. Default Tabs (Text-based)
- **Container Class**: `.tab`
- **Use Case**: Standard horizontal tabs with text labels
- **Example**: Lines 598-627 in ui-tabs.html

### 2. Colored Tabs
- **Container Classes**: `.tab .tab-{color}`
- **Available Colors**:
  - `.tab-primary` (primary blue)
  - `.tab-success` (green)
  - `.tab-danger` (red)
  - Other Bootstrap color variants
- **Example**: Lines 631-660 (primary), 709-750 (success), 799-840 (danger)

### 3. Icon Tabs
- **Container Class**: `.tab`
- **Tab Link Content**: Lucide icons using `<i class="align-middle" data-lucide="icon-name"></i>`
- **Use Case**: Compact navigation with icon-only labels
- **Example**: Lines 664-705 in ui-tabs.html
- **Common Icons**: home, settings, message-square

### 4. Colored Icon Tabs
- **Container Classes**: `.tab .tab-{color}`
- **Combines**: Icon tabs + color variants
- **Example**: Lines 709-750 in ui-tabs.html

### 5. Vertical Tabs
- **Container Classes**: `.tab .tab-vertical`
- **Layout**: Tabs displayed vertically on the left side
- **Example**: Lines 754-795 in ui-tabs.html

### 6. Colored Vertical Icon Tabs
- **Container Classes**: `.tab .tab-vertical .tab-{color}`
- **Combines**: Vertical layout + icons + color variants
- **Example**: Lines 799-840 in ui-tabs.html

## Key CSS Classes

### Navigation Elements
- `.nav.nav-tabs` - Tab navigation container
- `.nav-item` - Individual tab wrapper
- `.nav-link` - Clickable tab link
- `.nav-link.active` - Currently active tab

### Content Elements
- `.tab-content` - Container for all tab panels
- `.tab-pane` - Individual tab panel
- `.tab-pane.active` - Currently visible tab panel
- `.tab-title` - Optional heading inside tab content

## Bootstrap Attributes

### Required Data Attributes
- `data-bs-toggle="tab"` - Enables Bootstrap tab functionality on nav links
- `href="#tab-id"` - Links nav item to corresponding tab pane

### ARIA Attributes (Accessibility)
- `role="tablist"` - On `<ul class="nav nav-tabs">`
- `role="tab"` - On each `<a class="nav-link">`
- `role="tabpanel"` - On each `<div class="tab-pane">`

## Implementation Guidelines

### When Creating Tabs:
1. **Always use unique IDs** for each tab pane (e.g., `#tab-1`, `#tab-2`)
2. **Match href and ID**: The nav link's `href` must match the tab pane's `id`
3. **Set one active tab**: Exactly one `.nav-link` and one `.tab-pane` should have the `.active` class
4. **Include ARIA roles**: For accessibility compliance
5. **Use Lucide icons**: Icon implementation uses `data-lucide` attribute for SVG injection

### Color Customization:
- Add color class to the main `.tab` container
- Available: primary, secondary, success, danger, warning, info, light, dark

### Layout Options:
- **Horizontal** (default): No additional class needed
- **Vertical**: Add `.tab-vertical` to `.tab` container

### Content Best Practices:
- Use `.tab-title` for headings within tab content
- Keep content structure consistent across all tab panes
- Ensure content is meaningful even without JavaScript (progressive enhancement)

## Icon System

### Lucide Icons
- **Implementation**: `<i class="align-middle" data-lucide="icon-name"></i>`
- **Common Icons for Tabs**:
  - `home` - Dashboard/Home
  - `settings` - Settings/Configuration
  - `message-square` - Messages/Chat
  - `user` - Profile/Account
  - `list` - List view
  - `grid` - Grid view

## JavaScript Dependencies
- **Bootstrap 5**: Tab functionality via `data-bs-toggle="tab"`
- **Lucide Icons**: Icon rendering via `js/app.js`
- **No custom JavaScript required** for basic tab functionality

## Responsive Behavior
- Tabs are responsive by default with Bootstrap 5
- On smaller screens, tabs may wrap or scroll
- Consider using icon-only tabs for mobile layouts

## Common Use Cases

### Dashboard Navigation
Use default or colored tabs for switching between dashboard views.

### Settings Panels
Use vertical tabs for extensive settings with multiple categories.

### Modal/Dialog Content
Use icon tabs for compact space within modals.

### Data Visualization
Use colored tabs to differentiate data categories or chart types.

## Example Implementation Patterns

### Pattern 1: Standard Tab Group
```html
<div class="col-12 col-lg-6">
  <div class="tab">
    <ul class="nav nav-tabs" role="tablist">
      <li class="nav-item"><a class="nav-link active" href="#overview" data-bs-toggle="tab" role="tab">Overview</a></li>
      <li class="nav-item"><a class="nav-link" href="#details" data-bs-toggle="tab" role="tab">Details</a></li>
    </ul>
    <div class="tab-content">
      <div class="tab-pane active" id="overview" role="tabpanel">
        <h4 class="tab-title">Overview</h4>
        <p>Content here...</p>
      </div>
      <div class="tab-pane" id="details" role="tabpanel">
        <h4 class="tab-title">Details</h4>
        <p>Content here...</p>
      </div>
    </div>
  </div>
</div>
```

### Pattern 2: Colored Icon Tabs
```html
<div class="tab tab-primary">
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab">
        <i class="align-middle" data-lucide="home"></i>
      </a>
    </li>
    <!-- More tabs -->
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="tab-1" role="tabpanel">
      <!-- Content -->
    </div>
  </div>
</div>
```

## Reference File
Source: `/Applications/XAMPP/xamppfiles/htdocs/app.barril.cl/ui-tabs.html`
