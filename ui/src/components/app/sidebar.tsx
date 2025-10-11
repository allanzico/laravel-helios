import { Link } from '@tanstack/react-router';
import { Sheet, SheetContent, SheetTrigger } from '../../components/ui/sheet';
import { Button } from '../../components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '../../components/ui/tooltip';
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
} from 'lucide-react';

const navLinks = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/health', label: 'Health', icon: Heart },
   { to: '/errors', label: 'Errors', icon: AlertCircle },
  { to: '/logs', label: 'Logs', icon: FileText },
  { to: '/jobs', label: 'Jobs', icon: Clock },
  { to: '/tasks', label: 'Tasks', icon: Timer },
  { to: '/queries', label: 'Queries', icon: Database },
  { to: '/requests', label: 'Requests', icon: GitPullRequest },
];

const NavContent = ({ isCollapsed }: { isCollapsed: boolean }) => (
  <TooltipProvider>
    <nav className="grid items-start gap-1 px-2 text-sm font-medium">
      {navLinks.map(({ to, label, icon: Icon }) => (
        <Tooltip key={to} delayDuration={0}>
          <TooltipTrigger asChild>
            <Link
              to={to}
              className={`flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary ${
                isCollapsed ? 'justify-center' : ''
              }`}
              activeProps={{ className: 'bg-muted text-primary' }}
              activeOptions={{ exact: to === '/' }}
            >
              <Icon className="h-4 w-4" />
              {!isCollapsed && <span className="truncate">{label}</span>}
            </Link>
          </TooltipTrigger>
          {isCollapsed && <TooltipContent side="right">{label}</TooltipContent>}
        </Tooltip>
      ))}
    </nav>
  </TooltipProvider>
);

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
            {!isCollapsed ? <span className="transition-opacity">Scout 🔭</span> :<span className="transition-opacity">🔭</span>}
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
  const MobileNavContent = () => (
    <nav className="grid items-start gap-2 text-sm font-medium px-2">
      {navLinks.map(({ to, label, icon: Icon }) => (
        <Link
          key={to}
          to={to}
          className="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary"
          activeProps={{ className: 'bg-muted text-primary' }}
          activeOptions={{ exact: to === '/' }}
        >
          <Icon className="h-4 w-4" />
          {label}
        </Link>
      ))}
    </nav>
  );

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
          <span className="">Scout 🔭</span>
        </Link>
        <MobileNavContent />
      </SheetContent>
    </Sheet>
  );
}