import { useState, useEffect, useCallback } from "react";

export default function useUpcomingTransactions(params, refreshKey) {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);

    const fetchUpcoming = useCallback(() => {
        setLoading(true);

        axios.get('/api/transactions/upcoming', { params })
            .then(res => {
                setTransactions(res.data.data);
                setLoading(false);
            });
    }, [params]);

    useEffect(() => {
        fetchUpcoming();
    }, [fetchUpcoming, refreshKey]);

    return { transactions, loading };
}