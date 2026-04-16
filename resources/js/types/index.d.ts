export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    roles?: string[];
}

export interface Unit {
    id: string;
    name: string;
    color: string;
    unit_type: 'revenue' | 'service' | null;
}

export interface Employee {
    id: number;
    unit_id: string;
    manager_id: number | null;
    name: string | null;
    position: string;
    department: string;
    email: string | null;
    telegram: string | null;
    status: 'active' | 'vacancy' | 'fired';
    created_at: string;
    updated_at: string;
    unit?: Unit;
    manager?: Employee;
}

export type IdeaStatus =
    | 'new'
    | 'under_review'
    | 'approved'
    | 'rejected'
    | 'in_progress'
    | 'done';

export type IdeaPriority = 'high' | 'medium' | 'low';

export type IdeaFilterOperator = 'is' | 'is_not' | 'contains';

export interface Idea {
    id: number;
    display_id: string;
    unit_id: string;
    author_id: number;
    priority: IdeaPriority;
    status: IdeaStatus;
    title: string;
    description: string;
    impact: string;
    created_at: string;
    updated_at: string;
    unit?: Unit;
    author?: Employee;
}

export interface IdeaFilterState {
    col: 'unit_id' | 'status' | 'priority' | 'author_id' | 'title';
    op: IdeaFilterOperator;
    value: string;
}

export type RiskType = 'risk' | 'issue' | 'fuckup' | 'workaround';

export interface RiskEntry {
    id: number;
    display_id: string;
    type: RiskType;
    name: string;
    description: string;
    owner_name: string;
    status: string;
    entry_date: string;
    created_at: string;
    updated_at: string;
}

export type ServiceBilling = 'monthly' | 'yearly' | 'once';
export type ServiceStatus = 'active' | 'trial' | 'inactive';
export type ServiceCurrency = 'USD' | 'EUR' | 'UAH' | 'RUB';

export interface Service {
    id: number;
    name: string;
    url: string | null;
    category: string;
    unit_id: string;
    cost: string; // decimal serialized as string
    currency: ServiceCurrency;
    billing: ServiceBilling;
    next_payment: string | null;
    status: ServiceStatus;
    created_at: string;
    updated_at: string;
    unit?: Unit;
}

export type Translations = Record<string, unknown>;

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        // Non-null under auth middleware — Breeze pages rely on this.
        // For public pages (Welcome), guard with `if (auth.user)`.
        user: User;
    };
    units: Unit[];
    locale: string;
    supportedLocales: string[];
    translations: Translations;
    flash: {
        success: string | null;
        error: string | null;
    };
};
