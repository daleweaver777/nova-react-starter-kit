export default function Heading({
    title,
    description,
}: {
    title: string;
    description?: string;
}) {
    return (
        <header className="mb-8 flex flex-col gap-1">
            <h1 className="font-heading text-lg font-semibold">{title}</h1>
            {description && (
                <p className="text-sm text-muted-foreground">{description}</p>
            )}
        </header>
    );
}
