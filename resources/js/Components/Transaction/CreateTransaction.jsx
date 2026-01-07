import { useEffect, useState } from "react";
import SubmitTransaction from "./SubmitTransaction";

const CreateTransaction = ({ onSuccess }) => {

    const [loading, setLoading] = useState(false);
    
    const handleCreate = async (payload, setErrors, setLoading) => {
        try {
            await axios.post("/api/transactions", payload, { withCredentials: true });
        } catch (error) {
            if (error.response?.status === 422) {
                setErrors(error.response.data.errors);
            }
        } finally {
            setLoading(false);
        }
    };
    return (
        <SubmitTransaction
            onSubmit={handleCreate}
            submitLabel="Add Transaction"
            onSuccess={onSuccess}
        />
    );
};

export default CreateTransaction;