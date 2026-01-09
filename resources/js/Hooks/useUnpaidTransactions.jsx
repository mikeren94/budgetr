import { useEffect, useState, useCallback } from "react";
import axios from "axios";

export function useUnpaidTransactions() {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchUnpaid = useCallback(() => {
        setLoading(true);

        axios.get('/api/transactions/unpaid')
            .then(response => {
                setTransactions(response.data.data);
                setLoading(false);
            })
            .catch(err => {
                setError(err);
                setLoading(false);
            });
    }, []);

    useEffect(() => {
        fetchUnpaid();
    }, [fetchUnpaid]);

    return {
        transactions,
        loading,
        error,
        refresh: fetchUnpaid,   // <-- expose refresh
    };
}