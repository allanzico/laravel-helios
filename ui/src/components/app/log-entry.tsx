import { useState } from 'react';
import { Prism as SyntaxHighlighter } from 'react-syntax-highlighter';
import { atomDark } from 'react-syntax-highlighter/dist/esm/styles/prism';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { StatusBadge } from './status-badge';

// This regex will parse a standard Laravel log line
const LOG_REGEX = /^\[(.*?)\]\s(.*?)\.(.*?):\s(.*?)(?=\s?\{|$)/;

interface LogEntryProps {
    log: string;
}

export function LogEntry({ log }: LogEntryProps): JSX.Element {
    const [isOpen, setIsOpen] = useState(false);
    const match = log.match(LOG_REGEX);

    if (!match) {
        return <div className="pl-4 font-mono text-sm text-muted-foreground">{log}</div>;
    }

    const [, timestamp, level, message] = match;
    const contextIndex = log.indexOf('{');
    const context = contextIndex !== -1 ? log.substring(contextIndex) : '';
    let formattedContext = context;
    if (context && !context.includes('[stacktrace]')) {
        try {
            formattedContext = JSON.stringify(JSON.parse(context), null, 2);
        } catch (e) {
            console.error('Failed to parse log context as JSON:', e);
            // It's not valid JSON, leave it as is. This catch is a fallback.
        }
    }

    return (
        <Collapsible open={isOpen} onOpenChange={setIsOpen} className="group">
            <div className="flex items-start space-x-4 p-2 rounded-lg hover:bg-muted/50">
                <div className="font-mono text-xs text-muted-foreground w-48 flex-shrink-0">{timestamp}</div>
                <div>  <StatusBadge status={level} /></div>

                <div className="flex-grow font-mono text-sm">
                    {message}
                    {context && (
                        <CollapsibleTrigger asChild>
                            <Button variant="link" size="sm" className="ml-2 h-5 p-0">
                                {isOpen ? 'Hide Details' : 'Show Details'}
                            </Button>
                        </CollapsibleTrigger>
                    )}
                </div>
            </div>
            {context && (
                <CollapsibleContent className="p-2">
                    <SyntaxHighlighter language="json" style={atomDark} customStyle={{ borderRadius: '0.3rem',fontSize: '0.8rem', }}>
                        {formattedContext}
                    </SyntaxHighlighter>
                </CollapsibleContent>
            )}
        </Collapsible>
    );
}