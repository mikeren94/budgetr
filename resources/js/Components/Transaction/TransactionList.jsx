import Button from "../Utilities/Button";

const TransactionList = ({ 
    transactions, 
    loading, 
    onEdit, 
    onDelete,
    pagination,
    setPage
}) => {
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
                    className="
                        p-4 bg-white rounded-lg shadow 
                        flex flex-col sm:flex-row 
                        sm:justify-between sm:items-center
                        gap-4
                    "
                >
                    {/* LEFT SIDE */}
                    <div className="flex-1">
                        <p className="font-medium">{t.description || "No description"}</p>
                        <p className="text-sm text-gray-500">{t.date}</p>

                        {!t.category && (
                            <p className="text-xs text-yellow-700 bg-yellow-100 px-2 py-1 rounded inline-block mt-1">
                                No category assigned
                            </p>
                        )}
                    </div>

                    {/* RIGHT SIDE */}
                    <div
                        className="
                            flex flex-col sm:flex-row 
                            sm:items-center 
                            gap-3 sm:gap-4
                            w-full sm:w-auto
                        "
                    >
                        <span
                            className={`
                                font-semibold 
                                ${t.amount >= 0 ? "text-green-600" : "text-red-600"}
                                text-lg sm:text-base
                                text-right sm:text-left
                            `}
                        >
                            £{Number(t.amount).toFixed(2)}
                        </span>

                        <div className="flex gap-2 sm:gap-4 w-full sm:w-auto">
                            <Button
                                variant="secondary"
                                onClick={() => onEdit(t.id)}
                                className="flex-1 sm:flex-none"
                            >
                                Edit
                            </Button>

                            <Button
                                variant="danger"
                                onClick={() => onDelete(t.id)}
                                className="flex-1 sm:flex-none"
                            >
                                Delete
                            </Button>
                        </div>
                    </div>
                </div>
            ))}
            {pagination && (
                <div className="flex justify-between items-center mt-6">
                    <Button
                        variant="secondary"
                        disabled={!pagination.prev_page_url}
                        onClick={() => setPage(pagination.current_page - 1)}
                    >
                        Previous
                    </Button>

                    <span className="text-gray-600 text-sm">
                        Page {pagination.current_page} of {pagination.last_page}
                    </span>

                    <Button
                        variant="secondary"
                        disabled={!pagination.next_page_url}
                        onClick={() => setPage(pagination.current_page + 1)}
                    >
                        Next
                    </Button>
                </div>
            )}
        </div>
    );
};

export default TransactionList;