export type CatalogTimestamp = string | null;

export type Category = {
    id: number;
    key: string;
    name: string;
    created_at: CatalogTimestamp;
    updated_at: CatalogTimestamp;
};

export type Formation = {
    id: number;
    key: string;
    name: string;
    created_at: CatalogTimestamp;
    updated_at: CatalogTimestamp;
};

export type Position = {
    id: number;
    key: string;
    label_key: string;
    description_key: string | null;
    icon: string | null;
    abbreviation: string;
    created_at: CatalogTimestamp;
    updated_at: CatalogTimestamp;
};

export type StaffRole = {
    id: number;
    name: string;
    label_key: string;
    description_key: string | null;
    icon: string | null;
    created_at: CatalogTimestamp;
    updated_at: CatalogTimestamp;
};

export type BadgeScope = 'championship' | 'friendly' | 'career' | 'seasonal';

export type BadgeType = {
    id: number;
    name: string;
    label_key: string;
    description_key: string | null;
    icon: string | null;
    scope: BadgeScope;
    created_at: CatalogTimestamp;
    updated_at: CatalogTimestamp;
};

export type SportMode = {
    id: number;
    key: string;
    label_key: string;
    description_key: string | null;
    icon: string | null;
    categories: Category[];
    formations: Formation[];
    positions: Position[];
    created_at: CatalogTimestamp;
    updated_at: CatalogTimestamp;
};

export type CatalogMetricItem = {
    label: string;
    value: number | string;
    description: string;
};

export type CatalogSelectionItem = {
    id: number;
    label: string;
    description?: string | null;
};
