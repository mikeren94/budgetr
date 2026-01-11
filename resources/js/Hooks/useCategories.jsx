import { useState, useEffect } from "react";
import axios from "axios";

export const useCategories = () => {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);

    const fetchCategories = async () => {
        try {
            const response = await axios.get("/api/categories", {
                withCredentials: true,
            });
            setCategories(response.data.data);
        } catch (err) {
            console.error("Failed to fetch categories:", err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchCategories();
    }, []);

    return {
        categories,
        loading,
        refreshCategories: fetchCategories,
    };
};