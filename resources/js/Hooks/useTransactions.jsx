import { useEffect, useState, useCallback } from "react";
import axios from "axios";

export function useTransactions() {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchTransactions = useCallback(() => {
        setLoading(true);

        axios.get('/api/transactions')
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
        fetchTransactions();
    }, [fetchTransactions]);

    return { 
        transactions, 
        loading, 
        refresh: fetchTransactions, 
        error 
    };
}