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
    parent_id: string | null;
    head_id: number | null;
    deputy_id: number | null;
    founded_at: string | null;
    website: string | null;
    stage: string | null;
    description: string | null;
    legal_name: string | null;
    inn: string | null;
    employees_count?: number;
    head?: Employee;
    deputy?: Employee;
    children?: Unit[];
}

export type WorkStage = 'onboarding' | 'probation' | 'employee' | 'offboarding';

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
    work_stage: WorkStage;
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

export type InstructionType = 'instruction' | 'checklist';

export interface ChecklistItem {
    text: string;
    checked: boolean;
}

export interface Instruction {
    id: number;
    unit_id: string | null;
    title: string;
    type: InstructionType;
    content: string | null;
    checklist_items: ChecklistItem[] | null;
    created_by: number | null;
    created_at: string;
    updated_at: string;
    unit?: Unit;
    creator?: User;
}

export type OkrType = 'north_star' | 'objective' | 'key_result';
export type OkrStatus = 'active' | 'completed' | 'cancelled';

export interface OkrEntry {
    id: number;
    unit_id: string | null;
    type: OkrType;
    title: string;
    description: string | null;
    period: string;
    progress: number;
    status: OkrStatus;
    parent_id: number | null;
    sort_order: number;
    created_at: string;
    updated_at: string;
    unit?: Unit;
    children?: OkrEntry[];
}

export type RndStatus = 'idea' | 'research' | 'testing' | 'pilot' | 'scaling' | 'paused' | 'killed' | 'completed';
export type RndPriority = 'critical' | 'high' | 'medium' | 'low';

export interface RndProject {
    id: number;
    unit_id: string | null;
    title: string;
    description: string | null;
    owner_id: number;
    priority: RndPriority;
    status: RndStatus;
    budget: string | null;
    currency: string;
    deadline: string | null;
    success_criteria: string;
    kill_criteria: string;
    started_at: string | null;
    created_at: string;
    updated_at: string;
    unit?: Unit;
    owner?: Employee;
}

export type ProcessMaturity = 'not_documented' | 'documented_testing' | 'documented_digitized' | 'fully_automated';

export interface Process {
    id: number;
    unit_id: string | null;
    title: string;
    description: string | null;
    owner_id: number | null;
    category: string;
    maturity: ProcessMaturity;
    document_url: string | null;
    tool: string | null;
    sort_order: number;
    created_at: string;
    updated_at: string;
    unit?: Unit;
    owner?: Employee;
}

export type MeetingType = 'standup' | 'weekly' | 'monthly' | 'one_on_one' | 'retro' | 'planning' | 'other';
export type MeetingStatus = 'scheduled' | 'completed' | 'cancelled';

export interface Meeting {
    id: number;
    unit_id: string | null;
    title: string;
    type: MeetingType;
    scheduled_at: string;
    duration_minutes: number;
    location: string | null;
    agenda: string | null;
    notes: string | null;
    status: MeetingStatus;
    created_by: number | null;
    created_at: string;
    updated_at: string;
    unit?: Unit;
    creator?: User;
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
    activeUnitId: string | null;
    locale: string;
    supportedLocales: string[];
    translations: Translations;
    flash: {
        success: string | null;
        error: string | null;
    };
};
