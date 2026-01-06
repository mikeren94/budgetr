import { useEffect, useState } from "react";
import SubmitTransaction from "./SubmitTransaction";

const CreateTransaction = ({ id }) => {

    const [loading, setLoading] = useState(false);
    
    const handleCreate = async (payload, setErrors, setLoading, resetForm) => {
        try {
            await axios.post("/api/transactions", payload, { withCredentials: true });
            resetForm();
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
            submitLabel="Update Transaction"
        />
    );
};

export default EditTransaction;