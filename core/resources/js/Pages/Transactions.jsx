import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import CreateTransaction from "@/Components/Transaction/CreateTransaction";
import TransactionList from "@/Components/Transaction/TransactionList";
import { useTransactions } from "@/Hooks/useTransactions";
import { useState } from "react";
import EditTransaction from "@/Components/Transaction/EditTransaction";
const Transactions = () => {

    const { transactions, loading, pagination, setPage, refresh } = useTransactions();

    const [editingId, setEditingId] = useState(null);

    const handleDelete = async (id) => {
        const confirmed = window.confirm("Are you sure you want to delete this transaction?");
        if (!confirmed) return;

        try {
            await axios.delete(`/api/transactions/${id}`, { withCredentials: true });
            refresh();
        } catch (error) {
            console.error("Failed to delete transaction", error);
        }
    };

    const handleEdit = (id) => {
        setEditingId(id);
    };

    const handleSuccess = () => {
        refresh();
        setEditingId(null); // go back to create mode
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Transactions</h2>}
        >
            <Head title="Transactions" />

            <div className="pt-4">
                <div className="grid grid-cols-1 lg:grid-cols-6 gap-6 px-6">

                    {/* LEFT COLUMN */}
                    <div className="col-span-1 lg:col-span-2 bg-white rounded-lg shadow p-4">
                        {editingId ? (
                            <EditTransaction id={editingId} onSuccess={handleSuccess} onCancel={() => setEditingId(null)} />
                        ) : (
                            <CreateTransaction onSuccess={handleSuccess} />
                        )}
                    </div>

                    {/* RIGHT COLUMN */}
                    <div className="col-span-1 lg:col-span-4 bg-white rounded-lg shadow p-4">
                        <TransactionList
                            transactions={transactions}
                            loading={loading}
                            onEdit={handleEdit}
                            onDelete={handleDelete}
                            pagination={pagination}
                            setPage={setPage}
                        />
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default Transactions;