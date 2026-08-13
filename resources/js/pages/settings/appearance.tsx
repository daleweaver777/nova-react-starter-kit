import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import DensityTabs from '@/components/density-tabs';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    return (
        <>
            <Head title="Appearance settings" />

            <div className="flex flex-col gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Appearance settings</CardTitle>
                        <CardDescription>
                            Update the appearance settings for your account
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <AppearanceTabs />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Density</CardTitle>
                        <CardDescription>
                            Choose how much space the interface uses. Compact
                            tightens spacing and type across every screen.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <DensityTabs />
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'Appearance settings',
            href: editAppearance(),
        },
    ],
};
