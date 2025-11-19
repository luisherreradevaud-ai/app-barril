# UI Cards Component - Context Instructions

## Overview
This document provides context for implementing card UI components based on the AppStack Bootstrap 5 template (ui-cards.html).

## Card Structure

### Basic Card HTML Pattern
```html
<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Card Title</h5>
  </div>
  <div class="card-body">
    <p class="card-text">Card content goes here.</p>
  </div>
</div>
```

## Card Anatomy

### Core Components

#### 1. Card Container
- **Class**: `.card`
- **Purpose**: Main wrapper for all card content
- **Required**: Yes

#### 2. Card Image
- **Class**: `.card-img-top`
- **Purpose**: Top-aligned image
- **Position**: Typically first element inside `.card`
- **Element**: `<img>`
- **Example**: `<img class="card-img-top" src="path/to/image.jpg" alt="Description">`

#### 3. Card Header
- **Class**: `.card-header`
- **Purpose**: Contains title and/or navigation elements
- **Common Content**: Title, tabs, pills, badges
- **Example**: Lines 600-602, 614-616

#### 4. Card Body
- **Class**: `.card-body`
- **Purpose**: Main content area
- **Common Content**: Text, buttons, forms, any content
- **Example**: Lines 603-607, 617-620

#### 5. Card Title
- **Class**: `.card-title`
- **Purpose**: Main heading within card
- **Common Element**: `<h5>` with `mb-0` (no bottom margin)
- **Usage**: Inside `.card-header` or `.card-body`

#### 6. Card Text
- **Class**: `.card-text`
- **Purpose**: Standard paragraph text styling
- **Element**: `<p>`
- **Usage**: Inside `.card-body`

#### 7. Card Links
- **Class**: `.card-link`
- **Purpose**: Styled links with proper spacing
- **Usage**: Inside `.card-body`
- **Example**: Lines 605-606

## Card Variants

### 1. Card with Image and Links
**Use Case**: Display image with textual links for navigation

```html
<div class="card">
  <img class="card-img-top" src="img/photo.jpg" alt="Description">
  <div class="card-header">
    <h5 class="card-title mb-0">Card Title</h5>
  </div>
  <div class="card-body">
    <p class="card-text">Some quick example text...</p>
    <a href="#" class="card-link">Card link</a>
    <a href="#" class="card-link">Another link</a>
  </div>
</div>
```

**Reference**: Lines 597-609

### 2. Card with Image and Button
**Use Case**: Call-to-action card with image

```html
<div class="card">
  <img class="card-img-top" src="img/photo.jpg" alt="Description">
  <div class="card-header">
    <h5 class="card-title mb-0">Card Title</h5>
  </div>
  <div class="card-body">
    <p class="card-text">Some quick example text...</p>
    <a href="#" class="btn btn-primary">Go somewhere</a>
  </div>
</div>
```

**Reference**: Lines 611-622

### 3. Card with Image and List
**Use Case**: Image with list of items or features

```html
<div class="card">
  <img class="card-img-top" src="img/photo.jpg" alt="Description">
  <div class="card-header">
    <h5 class="card-title mb-0">Card Title</h5>
  </div>
  <ul class="list-group list-group-flush">
    <li class="list-group-item">Item 1</li>
    <li class="list-group-item">Item 2</li>
    <li class="list-group-item">Item 3</li>
  </ul>
</div>
```

**Reference**: Lines 624-636
**Note**: List replaces `.card-body` and uses `.list-group-flush` for borderless appearance

### 4. Card with Links (No Image)
**Use Case**: Simple content card with navigation links

```html
<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Card Title</h5>
  </div>
  <div class="card-body">
    <p class="card-text">Some quick example text...</p>
    <a href="#" class="card-link">Card link</a>
    <a href="#" class="card-link">Another link</a>
  </div>
</div>
```

**Reference**: Lines 638-649

### 5. Card with Button (No Image)
**Use Case**: Simple action card

```html
<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Card Title</h5>
  </div>
  <div class="card-body">
    <p class="card-text">Some quick example text...</p>
    <a href="#" class="btn btn-primary">Go somewhere</a>
  </div>
</div>
```

**Reference**: Lines 651-661

### 6. Card with List (No Image)
**Use Case**: List-based content display

```html
<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Card Title</h5>
  </div>
  <ul class="list-group list-group-flush">
    <li class="list-group-item">Item 1</li>
    <li class="list-group-item">Item 2</li>
    <li class="list-group-item">Item 3</li>
  </ul>
</div>
```

**Reference**: Lines 663-674

### 7. Card with Tabs
**Use Case**: Multi-view content within a card using tabs

```html
<div class="card">
  <div class="card-header">
    <ul class="nav nav-tabs card-header-tabs pull-right" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-1">Active</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-2">Link</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled" data-bs-toggle="tab" href="#tab-3">Disabled</a>
      </li>
    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content">
      <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
        <h5 class="card-title">Card with tabs</h5>
        <p class="card-text">Content...</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
      <div class="tab-pane fade" id="tab-2" role="tabpanel">
        <!-- Additional tab content -->
      </div>
    </div>
  </div>
</div>
```

**Reference**: Lines 676-711
**Key Classes**:
- `.card-header-tabs` - Styles tabs for card header
- `.pull-right` - Aligns tabs to the right
- `.tab-pane fade show active` - Active tab content
- `.tab-pane fade` - Inactive tab content

### 8. Card with Pills
**Use Case**: Multi-view content with pill-style navigation

```html
<div class="card">
  <div class="card-header">
    <ul class="nav nav-pills card-header-pills pull-right" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-4">Active</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-5">Link</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled" data-bs-toggle="tab" href="#tab-6">Disabled</a>
      </li>
    </ul>
  </div>
  <div class="card-body">
    <div class="tab-content">
      <div class="tab-pane fade show active" id="tab-4" role="tabpanel">
        <h5 class="card-title">Card with pills</h5>
        <p class="card-text">Content...</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
      <!-- Additional pill content -->
    </div>
  </div>
</div>
```

**Reference**: Lines 712-747
**Key Classes**:
- `.card-header-pills` - Styles pills for card header
- `.nav-pills` - Pill-style navigation
- `.pull-right` - Aligns pills to the right

## CSS Classes Reference

### Card Structure Classes
- `.card` - Main card container
- `.card-header` - Header section
- `.card-body` - Main content area
- `.card-footer` - Footer section (not shown in examples but available)

### Card Content Classes
- `.card-title` - Title heading (usually `<h5>` with `.mb-0`)
- `.card-subtitle` - Subtitle text (available but not shown in examples)
- `.card-text` - Paragraph text styling
- `.card-link` - Link styling with proper spacing

### Card Image Classes
- `.card-img-top` - Image at top of card
- `.card-img-bottom` - Image at bottom of card (available)
- `.card-img` - Full-width overlay image (available)

### Card List Classes
- `.list-group` - List container
- `.list-group-flush` - Removes outer borders for seamless card integration
- `.list-group-item` - Individual list item

### Card Navigation Classes
- `.card-header-tabs` - Tab navigation in header
- `.card-header-pills` - Pill navigation in header
- `.pull-right` - Right-align navigation

## Grid Layout

### Responsive Column Classes
Cards are typically placed within Bootstrap grid columns:

```html
<div class="row">
  <div class="col-12 col-md-6 col-lg-4">
    <!-- Card here -->
  </div>
</div>
```

**Common Patterns**:
- `col-12 col-md-6 col-lg-4` - 3 cards per row on large screens, 2 on medium, 1 on small
- `col-12 col-lg-6` - 2 cards per row on large screens, 1 on smaller
- `col-12` - Full width card

**Reference**: Lines 597, 611, 624, 638, 651, 663, 676, 712

## Implementation Guidelines

### When Creating Cards:

1. **Always wrap in `.card` container**
   - This provides the border, shadow, and spacing

2. **Use semantic HTML structure**
   - Header → Body → Footer (if needed)
   - Or: Image → Header → Body

3. **Title Formatting**
   - Use `<h5 class="card-title mb-0">` in headers
   - Use `<h5 class="card-title">` in body (with default margin)

4. **Image Best Practices**
   - Always include `alt` attribute
   - Use `.card-img-top` for top-aligned images
   - Image should be first child of `.card`

5. **List Integration**
   - Use `.list-group-flush` when list is inside card
   - List can replace `.card-body` for list-only cards
   - Individual items use `.list-group-item`

6. **Navigation Integration**
   - Tabs: Use `.nav-tabs.card-header-tabs`
   - Pills: Use `.nav-pills.card-header-pills`
   - Place navigation inside `.card-header`
   - Content goes in `.card-body` with `.tab-content`
   - Each tab needs unique ID matching href

7. **Disabled State**
   - Add `.disabled` class to `.nav-link` for disabled tabs/pills

8. **Responsive Considerations**
   - Cards automatically adapt to column width
   - Use appropriate grid classes for desired layout
   - Images should be responsive (automatically handled)

## Common Use Cases

### Dashboard Widgets
Use cards to contain metrics, charts, or status information.
```html
<div class="col-12 col-lg-4">
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">Total Sales</h5>
    </div>
    <div class="card-body">
      <h2>$12,345</h2>
      <p class="card-text text-success">+12% from last month</p>
    </div>
  </div>
</div>
```

### Product Display
Use image cards for product listings.
```html
<div class="col-12 col-md-6 col-lg-4">
  <div class="card">
    <img class="card-img-top" src="product.jpg" alt="Product">
    <div class="card-body">
      <h5 class="card-title">Product Name</h5>
      <p class="card-text">$99.99</p>
      <a href="#" class="btn btn-primary">Add to Cart</a>
    </div>
  </div>
</div>
```

### Settings Panels
Use cards with tabs for organized settings.
```html
<div class="col-12">
  <div class="card">
    <div class="card-header">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <a class="nav-link active" data-bs-toggle="tab" href="#general">General</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#security">Security</a>
        </li>
      </ul>
    </div>
    <div class="card-body">
      <div class="tab-content">
        <div class="tab-pane fade show active" id="general">
          <!-- General settings -->
        </div>
        <div class="tab-pane fade" id="security">
          <!-- Security settings -->
        </div>
      </div>
    </div>
  </div>
</div>
```

### Feature Lists
Use list cards for feature highlights.
```html
<div class="col-12 col-md-6">
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">Features</h5>
    </div>
    <ul class="list-group list-group-flush">
      <li class="list-group-item">Feature 1</li>
      <li class="list-group-item">Feature 2</li>
      <li class="list-group-item">Feature 3</li>
    </ul>
  </div>
</div>
```

## Advanced Customization

### Color Variants
Bootstrap 5 provides background color variants (not shown in examples but available):
- `.bg-primary`, `.bg-secondary`, `.bg-success`, `.bg-danger`, `.bg-warning`, `.bg-info`, `.bg-light`, `.bg-dark`
- Apply to `.card-header` or `.card` itself

### Border Variants
- `.border-primary`, `.border-secondary`, etc.
- Apply to `.card` for colored borders

### Text Alignment
- `.text-center`, `.text-start`, `.text-end`
- Apply to `.card-body`, `.card-header`, or specific elements

## JavaScript Dependencies

### Tab/Pill Functionality
- **Bootstrap 5**: Required for tab/pill switching
- **Attribute**: `data-bs-toggle="tab"` on nav links
- **No custom JavaScript required** for basic functionality

### Icon System
- **Lucide Icons**: For icons within cards (if needed)
- **Implementation**: `<i class="align-middle" data-lucide="icon-name"></i>`

## Accessibility

### ARIA Attributes
For cards with tabs/pills:
- `role="tablist"` on navigation `<ul>`
- `role="tab"` on navigation links
- `role="tabpanel"` on tab content divs

### Image Alt Text
- Always provide descriptive `alt` attributes on images
- Example: `alt="Product photo showing blue widget"`

### Link Context
- Ensure link text is descriptive
- Avoid generic "click here" text

## Performance Considerations

1. **Image Optimization**
   - Use appropriately sized images
   - Consider lazy loading for below-fold cards
   - Use responsive image techniques

2. **Grid Layout**
   - Cards automatically adapt to grid
   - Use appropriate column classes for performance

3. **Content Loading**
   - Consider skeleton screens for loading states
   - Load heavy content (charts, images) asynchronously if needed

## Reference File
Source: `/Applications/XAMPP/xamppfiles/htdocs/app.barril.cl/ui-cards.html`
