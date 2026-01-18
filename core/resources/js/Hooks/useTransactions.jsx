import { useEffect, useState, useCallback } from "react";
import axios from "axios";

export function useTransactions({ search, sortBy, sortDir }) {
    const [transactions, setTransactions] = useState([]);
    const [pagination, setPagination] = useState(null);
    const [page, setPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchTransactions = useCallback((pageNum = page) => {
        setLoading(true);

        axios.get(`/api/transactions`, {
            params: {
                page: pageNum,
                search,
                sortBy,
                sortDir
            }
        })
        .then(response => {
            const data = response.data;

            setTransactions(data.data);

            setPagination({
                current_page: data.current_page,
                last_page: data.last_page,
                next_page_url: data.next_page_url,
                prev_page_url: data.prev_page_url,
            });

            setLoading(false);
        })
        .catch(err => {
            setError(err);
            setLoading(false);
        });
    }, [page, search, sortBy, sortDir]);

    useEffect(() => {
        fetchTransactions(page);
    }, [page, search, sortBy, sortDir, fetchTransactions]);

    return {
        transactions,
        pagination,
        page,
        setPage,
        loading,
        error,
        refresh: () => fetchTransactions(page),
    };
}