import { useMemo } from "react";

export function useCategoryBreakdown(transactions) {
    return useMemo(() => {
        const map = {};

        transactions.forEach(t => {
            const cat = t.category;
            if (!cat) return;

            if (!map[cat.id]) {
                map[cat.id] = {
                    id: cat.id,
                    name: cat.name,
                    type: cat.type,
                    color: cat.color,
                    total: 0,
                };
            }

            map[cat.id].total += Number(t.amount);
        });

        const all = Object.values(map);

        return {
            income: all.filter(c => c.type === "income"),
            expenses: all.filter(c => c.type === "expense"),
        };
    }, [transactions]);
}