import { useEffect, useState } from 'react';
import { useRequestsPerMinuteQuery } from '../../queries/charts';
import { CartesianGrid, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { formatHex } from 'culori';

export function RequestsChart() {
    const { data, isLoading, isError } = useRequestsPerMinuteQuery();
    const [themeColors, setThemeColors] = useState({
        background: '#FFFFFF',
        border: '#EBEBEB',
        line: '#8884d8'
    });

    useEffect(() => {
        const computedStyle = getComputedStyle(document.documentElement);
        setThemeColors({
            background: formatHex(computedStyle.getPropertyValue('--background').trim()) ?? '#ffffff',
            border: formatHex(computedStyle.getPropertyValue('--border').trim()) ?? '#ebebeb',
            line: formatHex(computedStyle.getPropertyValue('--primary').trim()) ?? '#18181b'
        });
    }, []);


    if (isLoading) return <div>Loading Chart...</div>
    if (isError) return <div className="text-red-500">Could not load chart data.</div>

    return (
        <ResponsiveContainer width="100%" height={300}>

            <LineChart data={data}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                <XAxis dataKey="time" tickLine={false} axisLine={false} fontSize={12} />
                <YAxis allowDecimals={false} tickLine={false} axisLine={false} fontSize={12} />
                <Tooltip
                    contentStyle={{
                        backgroundColor: themeColors.background,
                        border: `1px solid ${themeColors.border}`,
                        borderRadius: 'var(--radius)'
                    }}
                />
                <Line type="monotone" dataKey="count" stroke={themeColors.line} strokeWidth={2} dot={true} />
            </LineChart>
        </ResponsiveContainer>
    );
}