import { useState, useEffect } from "react";
import axios from "axios";

export function useMonthlySummary(month) {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (!month) return;

        const fetchData = async () => {
            try {
                setLoading(true);
                setError(null);

                const response = await axios.get("/api/monthly-summary", {
                    params: { month },
                    withCredentials: true,
                });

                setData(response.data);
            } catch (err) {
                console.error("Failed to load monthly summary", err);
                setError("Failed to load monthly summary");
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [month]);

    return { data, loading, error };
}