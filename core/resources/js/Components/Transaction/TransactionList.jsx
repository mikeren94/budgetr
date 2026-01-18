import Button from "../Utilities/Button";
import SortableHeader from "../SortableHeader";
const TransactionList = ({
    transactions,
    loading,
    onEdit,
    onDelete,
    pagination,
    setPage,
    search,
    setSearch,
    sortBy,
    setSortBy,
    sortDir,
    setSortDir
}) => {
    if (loading) {
        return <p className="text-gray-500">Loading transactions…</p>;
    }

    if (!transactions || transactions.length === 0) {
        return <p className="text-gray-500">No transactions found.</p>;
    }

    return (
        <div className="overflow-x-auto rounded-lg shadow">
            <div className="mb-4">
                <input
                    type="text"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder="Search transactions…"
                    className="border border-gray-300 rounded px-3 py-2 w-full"
                />
            </div>
            <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                    <tr>
                        <SortableHeader label="Date" sortKey="date" sortBy={sortBy} sortDir={sortDir} setSortBy={setSortBy} setSortDir={setSortDir} />
                        <SortableHeader label="Description" sortKey="description" sortBy={sortBy} sortDir={sortDir} setSortBy={setSortBy} setSortDir={setSortDir} />
                        <th className="px-4 py-2 text-left text-sm font-medium text-gray-700">Category</th>
                        <SortableHeader label="Amount" sortKey="amount" sortBy={sortBy} sortDir={sortDir} setSortBy={setSortBy} setSortDir={setSortDir} />
                        <th className="px-4 py-2"></th>
                    </tr>
                </thead>

                <tbody className="divide-y divide-gray-200 bg-white">
                    {transactions.map(t => (
                        <tr key={t.id} className="hover:bg-gray-50">
                            <td className="px-4 py-2 text-sm text-gray-700">{t.formatted_date}</td>
                            <td className="px-4 py-2 text-sm">{t.description || "No description"}</td>
                            <td className="px-4 py-2 text-sm text-gray-500">
                                {t.category?.name || (
                                    <span className="text-yellow-700 bg-yellow-100 px-2 py-1 rounded text-xs">
                                        No category
                                    </span>
                                )}
                            </td>
                            <td className={`px-4 py-2 font-semibold ${t.amount >= 0 ? "text-green-600" : "text-red-600"}`}>
                                £{Number(t.amount).toFixed(2)}
                            </td>
                            <td className="px-4 py-2 flex gap-2">
                                <Button variant="secondary" onClick={() => onEdit(t.id)}>Edit</Button>
                                <Button variant="danger" onClick={() => onDelete(t.id)}>Delete</Button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
            {pagination && (
                <div className="flex justify-between items-center mt-6 px-2">
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