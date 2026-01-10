import { useState } from 'react';
import useUpcomingTransactions from '@/hooks/useUpcomingTransactions';

const UpcomingTransactions = () => {
    const [range, setRange] = useState('month'); // default filter
    const { transactions, loading } = useUpcomingTransactions(
        range === 'month' ? {} : { range }
    );

    return (
        <div className="card p-4">
            {/* Header */}
            <div className="flex items-center justify-between mb-3">
                <h2 className="text-lg font-semibold">Upcoming Transactions</h2>

                <select
                    value={range}
                    onChange={e => setRange(e.target.value)}
                    className="rounded-md border border-gray-300 bg-white px-2 py-1 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="month">End of Month</option>
                    <option value="7">Next 7 Days</option>
                    <option value="30">Next 30 Days</option>
                </select>
            </div>

            {/* Loading */}
            {loading && (
                <p className="text-sm text-gray-500">Loading…</p>
            )}

            {/* Empty state */}
            {!loading && transactions.length === 0 && (
                <p className="text-sm text-gray-500">No upcoming transactions</p>
            )}

            {/* List */}
            <ul className="space-y-2">
                {transactions.map(t => (
                    <li
                        key={t.id}
                        className="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm"
                    >
                        <div className="flex flex-col">
                            <p className="font-medium text-gray-800">{t.description}</p>
                            <p className="text-xs text-gray-500">{t.date}</p>
                        </div>

                        <p className="font-semibold text-gray-900">
                            £{Number(t.amount).toFixed(2)}
                        </p>
                    </li>
                ))}
            </ul>
        </div>
    );
}

export default UpcomingTransactions;