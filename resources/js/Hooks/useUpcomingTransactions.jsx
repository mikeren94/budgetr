import { useState, useEffect } from "react";

export default function useUpcomingTransactions({ range, endDate } = {}) {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let mounted = true;

        const params = {};

        if (range) params.range = range;
        if (endDate) params.end_date = endDate;

        axios.get('/api/transactions/upcoming', { params })
            .then(res => {
                if (mounted) setTransactions(res.data.data);
            })
            .finally(() => {
                if (mounted) setLoading(false);
            });

        return () => { mounted = false };
    }, [range, endDate]);

    return { transactions, loading };
}