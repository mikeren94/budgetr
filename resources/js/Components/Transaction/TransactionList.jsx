import Button from "../Utilities/Button";

const TransactionList = ({ transactions, loading, onEdit, onDelete }) => {
    if (loading) {
        return <p className="text-gray-500">Loading transactions…</p>;
    }

    if (!transactions || transactions.length === 0) {
        return <p className="text-gray-500">No transactions found.</p>;
    }

    return (
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

                        <Button variant="secondary" onClick={() => onEdit(t.id)}>
                            Edit
                        </Button>

                        <Button variant="danger" onClick={() => onDelete(t.id)}>
                            Delete
                        </Button>
                    </div>
                </div>
            ))}
        </div>
    );
};

export default TransactionList;