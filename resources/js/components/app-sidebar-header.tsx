import { Breadcrumbs } from '@/components/breadcrumbs';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

const EMPTY_BREADCRUMBS: BreadcrumbItemType[] = [];

export function AppSidebarHeader({
    breadcrumbs = EMPTY_BREADCRUMBS,
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    return (
        <header className="flex h-14 shrink-0 items-center gap-2 border-b px-4">
            <SidebarTrigger className="-ml-1" />
            <Separator
                orientation="vertical"
                className="mr-2 data-vertical:h-4 data-vertical:self-auto"
            />
            <Breadcrumbs breadcrumbs={breadcrumbs} />
        </header>
    );
}
