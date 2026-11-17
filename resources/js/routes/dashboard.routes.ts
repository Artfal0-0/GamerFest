import { NavItem } from '@/types';
import { Gamepad2, Globe, LayoutGrid } from 'lucide-vue-next';

// Register new routes
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        requiredAdmin: true,
        icon: LayoutGrid,
    },
    {
        title: 'Inscripciones',
        href: '/inscripciones',
        requiredAdmin: true,
        icon: Gamepad2,
    },
    {
        title: 'Reportes',
        href: '/reportes',
        requiredAdmin: true,
        icon: Gamepad2,
    },
    {
        title: 'Mis Inscripciones',
        href: '/mis_inscripciones',
        icon: Gamepad2,
    },
    {
        title: 'Mi equipo',
        href: '/mis_equipos',
        icon: Gamepad2,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Web',
        href: '/',
        icon: Globe,
    },
];

export { footerNavItems, mainNavItems };
