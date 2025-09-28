# Toast Configuration

This directory contains configuration files for the frontend application.

## Toast Configuration (`toast.ts`)

Configuration for Sonner toast notifications used throughout the application.

### Structure

- **`toaster`** - Settings for the main Toaster component
- **`delays`** - Timing delays for different toast types to prevent overlapping
- **`defaults`** - Default options for each toast type (success, error, warning, info)
- **`initialLoadDelay`** - Delay before showing toasts on initial page load

### Usage

```typescript
import { toastConfig, type FlashMessages } from '@/config';

// Used in app.ts for Toaster component
h(Toaster, { 
    class: 'pointer-events-auto',
    ...toastConfig.toaster,
})

// Used for showing toasts with proper delays
toast.success(message, toastConfig.defaults.success);
setTimeout(() => toast.error(message, toastConfig.defaults.error), toastConfig.delays.error);
```

### Customization

To modify toast behavior, edit the values in `toast.ts`:

- Position: Change `position` in `toaster` config
- Duration: Modify `duration` in `defaults` for specific types
- Delays: Adjust `delays` object to change timing between multiple toasts
- Visual: Update `richColors`, `closeButton`, etc. in `toaster` config