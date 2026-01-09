import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import CreateTransaction from "@/Components/Transaction/CreateTransaction";
import TransactionList from "@/Components/Transaction/TransactionList";
import { useTransactions } from "@/Hooks/useTransactions";

const Transactions = () => {

    const { transactions: transactions, loading: loading, refresh: refresh } = useTransactions();

    
    const handleDelete = async (id) => {
        const confirmed = window.confirm("Are you sure you want to delete this transaction?");

        if (!confirmed) return;

        try {
            await axios.delete(`/api/transactions/${id}`, {
                withCredentials: true,
            });

            // Refresh the list
            refresh();
        } catch (error) {
            console.error("Failed to delete transaction", error);
        }
    };
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Transactions
                </h2>
            }
        >
            <Head title="Transactions" />

            <div className="py-12">
                <div className="grid grid-cols-1 md:grid-cols-6 gap-6 px-6">
                    <div className="col-span-2 bg-white rounded-lg shadow p-4">
                        <CreateTransaction onSuccess={refresh} />
                    </div>
                    <div className="col-span-4 bg-white rounded-lg shadow p-4">
                        <TransactionList 
                            transactions={transactions} 
                            loading={loading}
                            onEdit={() => {}}
                            onDelete={handleDelete}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )
};

export default Transactions;