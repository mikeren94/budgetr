import Button from "../Utilities/Button";
import { useState, useEffect } from "react";
import SubmitTransaction from "./SubmitTransaction";

const TransactionList = ({refreshFlag}) => {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [editingTransaction, setEditingTransaction] = useState(null);
    const [loadingEdit, setLoadingEdit] = useState(false);
    
    const fetchTransactions = async () => {
        try {
            const month = new Date().toISOString().slice(0, 7); // "2026-01"
            const response = await axios.get(`/api/transactions?month=${month}`, {
                withCredentials: true,
            });

            setTransactions(response.data.data);
        } catch (error) {
            console.error("Failed to load transactions", error);
        } finally {
            setLoading(false);
        }
    };

    const startEditing = async (id) => {
        setLoadingEdit(true);

        try {
            const response = await axios.get(`/api/transactions/${id}`, {
                withCredentials: true,
            });

            setEditingTransaction(response.data.data);
        } catch (error) {
            console.error("Failed to load transaction", error);
        } finally {
            setLoadingEdit(false);
        }
    };

    const submitEdit = async (payload, setErrors, setLoading) => {
        console.log(payload);
        try {
            await axios.put(`/api/transactions/${editingTransaction.id}`, payload, {
                withCredentials: true,
            });

            // Close the form
            setEditingTransaction(null);

            // Refresh the list
            fetchTransactions();
        } catch (error) {
            if (error.response?.status === 422) {
                setErrors(error.response.data.errors);
            }
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        fetchTransactions();
    }, [refreshFlag]);
    return (
        <div className="space-y-4">
            <h2 className="text-xl font-semibold text-gray-800">
                This Month’s Transactions
            </h2>

            {transactions.length === 0 && (
                <p className="text-gray-500">No transactions yet this month.</p>
            )}

            <div className="space-y-3">
                {transactions.map((t) => (
                    <div
                        key={t.id}
                        className="p-4 bg-white rounded-lg shadow flex justify-between items-center"
                    >
                        <div>
                            <p className="font-medium">{t.description || "No description"}</p>
                            <p className="text-sm text-gray-500">{t.date}</p>
                        </div>

                        <div className="flex items-center gap-4">
                            <span
                                className={`font-semibold ${
                                    t.amount >= 0 ? "text-green-600" : "text-red-600"
                                }`}
                            >
                            £{Number(t.amount).toFixed(2)}
                            </span>

                            <Button
                                variant="secondary"
                                onClick={() => startEditing(t.id)}
                            >
                                Edit
                            </Button>
                        </div>
                    </div>
                ))}
            </div>

            {editingTransaction && (
                <div className="mt-6">
                    <SubmitTransaction
                        initialValues={editingTransaction}
                        submitLabel="Update Transaction"
                        onSuccess={() => fetchTransactions()}
                        onSubmit={(payload, setErrors, setLoading) => submitEdit(payload, setErrors, setLoading)}
                    />
                </div>
            )}
        </div>
    );
}

export default TransactionList;