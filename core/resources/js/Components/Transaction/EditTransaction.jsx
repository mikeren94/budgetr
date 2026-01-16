import { useEffect, useState } from "react";
import SubmitTransaction from "./SubmitTransaction";
import Button from "../Utilities/Button";

const EditTransaction = ({ id, onSuccess, onCancel }) => {
    const [transaction, setTransaction] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let isCurrent = true; // ⭐ flag to ignore stale responses
        setLoading(true);

        const fetchTransaction = async () => {
            const response = await axios.get(`/api/transactions/${id}`, {
                withCredentials: true,
            });

            if (isCurrent) {
                setTransaction(response.data.data);
                setLoading(false);
            }
        };

        fetchTransaction();

        return () => {
            isCurrent = false; // ⭐ mark this request as stale
        };
    }, [id]);

    const handleUpdate = async (payload, setErrors, setFormLoading) => {
        try {
            await axios.put(`/api/transactions/${id}`, payload, {
                withCredentials: true,
            });
            return true;
        } catch (error) {
            if (error.response?.status === 422) {
                setErrors(error.response.data.errors);
            }
            return false;
        } finally {
            setFormLoading(false);
        }
    };

    if (loading) return <p>Loading…</p>;

    return (
        <>
        <SubmitTransaction
            key={id}              // ⭐ forces a full remount when id changes
            initialValues={transaction}
            onSubmit={handleUpdate}
            onSuccess={onSuccess}
            submitLabel="Update Transaction"
        />
        <Button
            className="mt-4 px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition w-full"

            onClick={onCancel}
        >Cancel</Button>
        </>
    );
};

export default EditTransaction;