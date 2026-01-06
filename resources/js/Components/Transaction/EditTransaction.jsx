import { useEffect, useState } from "react";
import SubmitTransaction from "./SubmitTransaction";

const EditTransaction = ({ id }) => {
    const [transaction, setTransaction] = useState(null);
    const [loading, setLoading] = useState(true);

    const fetchTransaction = async () => {
        const response = await axios.get(`/api/transactions/${id}`, {
            withCredentials: true,
        });
        setTransaction(response.data.data);
        setLoading(false);
    };

    useEffect(() => {
        fetchTransaction();
    }, []);

    const handleUpdate = async (payload, setErrors, setLoading) => {
        try {
            await axios.put(`/api/transactions/${id}`, payload, {
                withCredentials: true,
            });
        } catch (error) {
            if (error.response?.status === 422) {
                setErrors(error.response.data.errors);
            }
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <p>Loading…</p>;

    return (
        <SubmitTransaction
            initialValues={transaction}
            onSubmit={handleUpdate}
            submitLabel="Update Transaction"
        />
    );
};

export default EditTransaction;