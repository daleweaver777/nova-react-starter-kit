import type { LucideIcon } from 'lucide-react';
import { FoldVertical, UnfoldVertical } from 'lucide-react';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import type { Density } from '@/hooks/use-density';
import { useDensity } from '@/hooks/use-density';

const tabs: { value: Density; icon: LucideIcon; label: string }[] = [
    { value: 'default', icon: UnfoldVertical, label: 'Standard' },
    { value: 'compact', icon: FoldVertical, label: 'Compact' },
];

export default function DensityToggleTab({
    className,
}: {
    className?: string;
}) {
    const { density, updateDensity } = useDensity();

    return (
        <ToggleGroup
            aria-label="Density"
            variant="outline"
            spacing={0}
            value={[density]}
            onValueChange={(value) => {
                const [next] = value as Density[];

                if (next) {
                    updateDensity(next);
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
