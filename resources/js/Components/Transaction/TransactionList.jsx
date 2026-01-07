import Button from "../Utilities/Button";
import { useState, useEffect } from "react";
import EditTransaction from "./EditTransaction";

const TransactionList = ({ refreshFlag }) => {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [editingId, setEditingId] = useState(null);

    const fetchTransactions = async () => {
        try {
            const month = new Date().toISOString().slice(0, 7);
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

    const handleDelete = async (id) => {
        const confirmed = window.confirm("Are you sure you want to delete this transaction?");

        if (!confirmed) return;

        try {
            await axios.delete(`/api/transactions/${id}`, {
                withCredentials: true,
            });

            // Refresh the list
            fetchTransactions();
        } catch (error) {
            console.error("Failed to delete transaction", error);
        }
    };
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
                                onClick={() => setEditingId(t.id)}
                            >
                                Edit
                            </Button>

                            <Button
                                variant="danger"
                                onClick={() => handleDelete(t.id)}
                            >
                                Delete
                            </Button>

                        </div>
                    </div>
                ))}
            </div>

            {editingId && (
                <div className="mt-6">
                    <EditTransaction
                        id={editingId}
                        onSuccess={() => {
                            setEditingId(null);
                            fetchTransactions();
                        }}
                    />
                </div>
            )}
        </div>
    );
};

export default TransactionList;