import { useEffect, useState } from "react";
import SubmitTransaction from "./SubmitTransaction";

const CreateTransaction = ({ onSuccess }) => {
    
    const handleCreate = async (payload, setErrors, setFormLoading) => {
        try {
            await axios.post("/api/transactions", payload, { withCredentials: true });
            onSuccess();
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
    return (
        <SubmitTransaction
            onSubmit={handleCreate}
            submitLabel="Add Transaction"
            onSuccess={onSuccess}
        />
    );
};

export default CreateTransaction;