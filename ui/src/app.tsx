import { useState } from 'react';
import { Outlet } from '@tanstack/react-router';
import { Toaster } from 'sonner';
import { DesktopSidebar, MobileSidebar } from './components/app/sidebar.tsx';

export default function App() {
  const [isCollapsed, setIsCollapsed] = useState(false);

  return (
    <div
      className={`grid min-h-screen w-full transition-[grid-template-columns]
                  ${isCollapsed ? 'md:grid-cols-[56px_1fr]' : 'md:grid-cols-[220px_1fr] lg:grid-cols-[280px_1fr]'}`}
    >
      <DesktopSidebar isCollapsed={isCollapsed} setIsCollapsed={setIsCollapsed} />
      <div className="flex flex-col">
        <header className="flex h-14 items-center gap-4 border-b bg-muted/40 px-4 lg:h-[60px] lg:px-6">
          <MobileSidebar />
          <div className="w-full flex-1">
            {/* Header content can go here */}
          </div>
        </header>
        <main className="flex flex-1 flex-col gap-4 p-4 lg:gap-6 lg:p-6">
          <Outlet />
        </main>
      </div>
      <Toaster position="top-right" richColors />
    </div>
  );
}