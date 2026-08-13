import { Toaster as Sonner, type ToasterProps } from "sonner"
import { CircleCheckIcon, InfoIcon, TriangleAlertIcon, OctagonXIcon, Loader2Icon } from "lucide-react"

// Two deviations from the shadcn registry version, both from the Laravel
// starter kit. `shadcn apply --preset` overwrites this file and drops them:
//   - useAppearance() instead of next-themes, which this app does not use.
//   - useFlashToast(), the only caller of that hook. Without it every
//     Inertia::flash('toast', ...) from the server goes nowhere.
import { useAppearance } from "@/hooks/use-appearance"
import { useFlashToast } from "@/hooks/use-flash-toast"

const Toaster = ({ ...props }: ToasterProps) => {
  const { appearance } = useAppearance()

  useFlashToast()

  return (
    <Sonner
      theme={appearance}
      className="toaster group"
      icons={{
        success: (
          <CircleCheckIcon className="size-4" />
        ),
        info: (
          <InfoIcon className="size-4" />
        ),
        warning: (
          <TriangleAlertIcon className="size-4" />
        ),
        error: (
          <OctagonXIcon className="size-4" />
        ),
        loading: (
          <Loader2Icon className="size-4 animate-spin" />
        ),
      }}
      style={
        {
          "--normal-bg": "var(--popover)",
          "--normal-text": "var(--popover-foreground)",
          "--normal-border": "var(--border)",
          "--border-radius": "var(--radius)",
        } as React.CSSProperties
      }
      toastOptions={{
        classNames: {
          toast: "cn-toast",
        },
      }}
      {...props}
    />
  )
}

export { Toaster }
