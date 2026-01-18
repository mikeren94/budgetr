// /hooks/useMonthlyTransactions.js

import { useEffect, useState } from "react";

export function useMonthlyTransactions(month) {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!month) return;

        setLoading(true);

        axios
            .get("/api/transactions/monthly", {
                params: { month },
            })
            .then((res) => setTransactions(res.data.data))
            .finally(() => setLoading(false));
    }, [month]);

    return { transactions, loading };
}