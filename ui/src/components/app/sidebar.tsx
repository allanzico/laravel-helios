import { Link, useRouterState } from '@tanstack/react-router';
import { useEffect, useMemo, useState } from 'react';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import {
  PanelLeft,
  FileText,
  Clock,
  Timer,
  Database,
  LayoutDashboard,
  GitPullRequest,
  ChevronLeft,
  Heart,
  AlertCircle,
  Sun,
  ChevronDown,
} from 'lucide-react';

const navSections = [
  {
    label: 'Overview',
    icon: LayoutDashboard,
    defaultTo: '/',
    links: [],
  },
  {
    label: 'Operations',
    icon: Heart,
    defaultTo: '/health',
    links: [
      { to: '/health', label: 'Health', icon: Heart },
      { to: '/jobs', label: 'Queues', icon: Clock },
      { to: '/tasks', label: 'Scheduler', icon: Timer },
    ],
  },
  {
    label: 'Performance',
    icon: GitPullRequest,
    defaultTo: '/requests',
    links: [
      { to: '/requests', label: 'Requests', icon: GitPullRequest },
      { to: '/queries', label: 'Queries', icon: Database },
    ],
  },
  {
    label: 'Events',
    icon: AlertCircle,
    defaultTo: '/errors',
    links: [
      { to: '/errors', label: 'Errors', icon: AlertCircle },
      { to: '/logs', label: 'Logs', icon: FileText },
    ],
  },
];

const isPathActive = (pathname: string, to: string) => (
  to === '/' ? pathname === '/' : pathname === to || pathname.startsWith(`${to}/`)
);

const NavContent = ({ isCollapsed }: { isCollapsed: boolean }) => {
  const pathname = useRouterState({ select: state => state.location.pathname });
  const activeSection = useMemo(() => (
    navSections.find(section => (
      isPathActive(pathname, section.defaultTo)
      || section.links.some(link => isPathActive(pathname, link.to))
    ))?.label
  ), [pathname]);
  const [openSections, setOpenSections] = useState<Set<string>>(() => (
    activeSection && activeSection !== 'Overview' ? new Set([activeSection]) : new Set()
  ));

  useEffect(() => {
    if (!activeSection || activeSection === 'Overview') {
      return;
    }

    setOpenSections(previous => new Set(previous).add(activeSection));
  }, [activeSection]);

  const toggleSection = (label: string) => {
    setOpenSections((previous) => {
      const next = new Set(previous);

      if (next.has(label)) {
        next.delete(label);
      } else {
        next.add(label);
      }

      return next;
    });
  };

  return (
    <TooltipProvider>
      <nav className="grid items-start gap-1 px-2 text-sm font-medium">
        {navSections.map((section) => {
          const Icon = section.icon;
          const isActive = activeSection === section.label;

          if (isCollapsed || section.links.length === 0) {
            return (
              <Tooltip key={section.label} delayDuration={0}>
                <TooltipTrigger asChild>
                  <Link
                    to={section.defaultTo}
                    className={`flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary ${
                      isCollapsed ? 'justify-center' : ''
                    } ${isActive ? 'bg-muted text-primary' : ''}`}
                  >
                    <Icon className="h-4 w-4 shrink-0" />
                    {!isCollapsed && <span className="truncate">{section.label}</span>}
                  </Link>
                </TooltipTrigger>
                {isCollapsed && <TooltipContent side="right">{section.label}</TooltipContent>}
              </Tooltip>
            );
          }

          const isOpen = openSections.has(section.label);

          return (
            <div key={section.label} className="grid gap-1">
              <button
                type="button"
                className={`flex items-center gap-3 rounded-lg px-3 py-2 text-left text-muted-foreground transition-all hover:text-primary ${
                  isActive ? 'bg-muted text-primary' : ''
                }`}
                aria-expanded={isOpen}
                onClick={() => toggleSection(section.label)}
              >
                <Icon className="h-4 w-4 shrink-0" />
                <span className="min-w-0 flex-1 truncate">{section.label}</span>
                <ChevronDown className={`h-4 w-4 shrink-0 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
              </button>

              {isOpen && (
                <div className="ml-5 grid gap-1 border-l pl-2">
                  {section.links.map(({ to, label, icon: LinkIcon }) => (
                    <Link
                      key={to}
                      to={to}
                      className="flex items-center gap-2 rounded-md px-3 py-2 text-muted-foreground transition-all hover:text-primary"
                      activeProps={{ className: 'bg-muted text-primary' }}
                      activeOptions={{ exact: to === '/' }}
                    >
                      <LinkIcon className="h-3.5 w-3.5 shrink-0" />
                      <span className="truncate">{label}</span>
                    </Link>
                  ))}
                </div>
              )}
            </div>
          );
        })}
      </nav>
    </TooltipProvider>
  );
};

interface DesktopSidebarProps {
    isCollapsed: boolean;
    setIsCollapsed: React.Dispatch<React.SetStateAction<boolean>>;
}

export function DesktopSidebar({ isCollapsed, setIsCollapsed }: DesktopSidebarProps) {
  return (
    <div className="hidden border-r bg-muted/40 md:block relative">
      <div className="flex h-full max-h-screen flex-col">
        <div className={`flex h-14 items-center border-b ${isCollapsed ? 'justify-center' : 'px-4 lg:px-6'}`}>
          <Link to="/" className="flex items-center gap-2 font-semibold">
            <Sun className="h-4 w-4 text-primary" />
            {!isCollapsed && <span className="transition-opacity">Helios</span>}
          </Link>
        </div>
        <div className="flex-1 overflow-auto py-2">
          <NavContent isCollapsed={isCollapsed} />
        </div>
      </div>
      <Button
        variant="outline"
        size="icon"
        className={`absolute top-4 -right-4 h-8 w-8 rounded-full transition-transform ${isCollapsed ? 'rotate-180' : ''}`}
        onClick={() => setIsCollapsed(prev => !prev)}
      >
        <ChevronLeft className="h-4 w-4" />
        <span className="sr-only">Toggle sidebar</span>
      </Button>
    </div>
  );
}

export function MobileSidebar() {
  return (
    <Sheet>
      <SheetTrigger asChild>
        <Button variant="outline" size="icon" className="shrink-0 md:hidden">
          <PanelLeft className="h-5 w-5" />
          <span className="sr-only">Toggle navigation menu</span>
        </Button>
      </SheetTrigger>
      <SheetContent side="left" className="flex flex-col">
        <Link to="/" className="flex items-center gap-2 font-semibold border-b pb-4 px-2 mb-4">
          <Sun className="h-4 w-4 text-primary" />
          <span>Helios</span>
        </Link>
        <NavContent isCollapsed={false} />
      </SheetContent>
    </Sheet>
  );
}
