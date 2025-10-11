import { useMutation, useQueryClient } from '@tanstack/react-query';
import { purgeTable } from '../api/purge';

export const usePurgeMutation = (queryKey: string[]) => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: (table: string) => purgeTable(table),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey });
        },
    });
};