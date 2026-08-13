import type { LucideIcon } from 'lucide-react';
import { Monitor, Moon, Sun } from 'lucide-react';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';

const tabs: { value: Appearance; icon: LucideIcon; label: string }[] = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
];

export default function AppearanceToggleTab({
    className,
}: {
    className?: string;
}) {
    const { appearance, updateAppearance } = useAppearance();

    return (
        <ToggleGroup
            aria-label="Appearance"
            variant="outline"
            spacing={0}
            value={[appearance]}
            onValueChange={(value) => {
                const [next] = value as Appearance[];

                if (next) {
                    updateAppearance(next);
                }
            }}
            className={className}
        >
            {tabs.map(({ value, icon: Icon, label }) => (
                <ToggleGroupItem key={value} value={value}>
                    <Icon />
                    {label}
                </ToggleGroupItem>
            ))}
        </ToggleGroup>
    );
}
