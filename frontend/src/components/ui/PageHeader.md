# PageHeader Component Usage Guide

## Overview
The PageHeader component provides a consistent, reusable header for all admin pages with clean styling and flexible customization options.

## Basic Usage

```vue
<template>
  <Layout>
    <PageHeader
      title="Your Page Title"
      description="A brief description of what this page does"
      :title-icon="YourIcon"
    />
    
    <!-- Your page content here -->
  </Layout>
</template>

<script setup>
import PageHeader from '@/components/ui/PageHeader.vue'
import { YourIcon } from 'lucide-vue-next'
</script>
```

## Advanced Usage with Actions and Stats

```vue
<template>
  <PageHeader
    title="Alumni Directory"
    description="Manage and view all alumni records"
    :title-icon="IconUsers"
    badge="Directory"
    badge-type="primary"
  >
    <!-- Actions slot for buttons -->
    <template #actions>
      <button class="btn btn-primary-custom">
        <IconPlus class="w-4 h-4" />
        Add Alumni
      </button>
      <button class="btn btn-neutral">
        <IconRefreshCw class="w-4 h-4" />
        Refresh
      </button>
    </template>

    <!-- Subtitle slot for stats/filters -->
    <template #subtitle>
      <div class="flex gap-4 items-center text-sm text-gray-600">
        <div class="flex items-center gap-2">
          <span class="font-medium">Total:</span>
          <span class="badge badge-ghost">{{ totalItems }}</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="font-medium">Filtered:</span>
          <span class="badge badge-accent">{{ filteredItems }}</span>
        </div>
      </div>
    </template>
  </PageHeader>
</template>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | String | Required | Main page title |
| `description` | String | '' | Optional description text |
| `titleIcon` | Component | null | Lucide icon component |
| `badge` | String | '' | Optional badge text |
| `badgeType` | String | 'primary' | Badge color variant |

## Badge Types
- `primary` - Blue
- `secondary` - Red  
- `accent` - Green
- `ghost` - Orange
- `neutral` - Gray
- `success` - Green
- `warning` - Yellow
- `error` - Red

## Slots

### `#actions`
For buttons and action controls on the right side of the header.

### `#subtitle` 
For additional information like stats, filters status, or secondary navigation.

### `#breadcrumbs`
For navigation breadcrumbs (appears below description).

## Dark Theme Support

For pages with dark backgrounds (like AdminDocumentRequests), wrap the PageHeader with `page-header-dark` class:

```vue
<div class="page-header-dark">
  <PageHeader title="Document Requests" ... />
</div>
```

## Responsive Design

The component automatically adapts to different screen sizes:
- Desktop: Side-by-side title and actions
- Tablet: Stacked layout with proper spacing
- Mobile: Full-width stacked layout with smaller text

## Styling

The component uses CSS custom properties from `app.css`:
- `--color-primary` for main accents
- `--color-text-primary` for main text
- `--color-text-secondary` for descriptions
- `--color-border-light` for borders

This ensures consistency with your app's color system.
