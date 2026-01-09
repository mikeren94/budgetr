const UnpaidTransactions = ({ transactions, loading, onMarkPaid }) => {

    if (loading) {
        return <p className="text-gray-500 italic">Locaing transactions...</p>
    }

    if (!transactions || transactions.length === 0) {
        return <p className="text-gray-500 italic">No unpaid transactions.</p>
    }

    return (
        <>
            <h2 className="text-lg font-semibold mb-4 text-red-700">
                Unpaid Transactions
            </h2>

            <ul className="space-y-3">
                {transactions.map((t) => (
                    <li
                        key={t.id}
                        className="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                    >
                        <div>
                            <p className="font-medium">{t.description}</p>
                            <p className="text-sm text-gray-500">
                                Due: {t.date}
                            </p>
                        </div>

                        <div className="flex items-center gap-4">
                            <p className="font-semibold text-red-600">
                                £{t.amount}
                            </p>

                            <button
                                className="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                                onClick={() => onMarkPaid(t.id)}
                            >
                                Mark Paid
                            </button>
                        </div>
                    </li>    
                ))}
            </ul>
        </>
    )
}

export default UnpaidTransactions;