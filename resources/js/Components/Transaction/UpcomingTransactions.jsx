import { useState } from 'react';
import useUpcomingTransactions from '@/hooks/useUpcomingTransactions';
import Dropdown from '../Utilities/Dropdown';
const UpcomingTransactions = () => {
    const [range, setRange] = useState(null);
    const { transactions, loading } = useUpcomingTransactions(
        range?.value === 'month'
            ? {}
            : range?.value
                ? { range: range.value }
                : {}
    );
    const apiRange = range?.value; // "7", "30", "month"
    return (
        <div className="card p-4">
            {/* Header */}
            <div className="flex items-center justify-between mb-3">
                <h2 className="text-lg font-semibold">Upcoming Transactions</h2>

                 <div className="w-40">
                    <Dropdown
                        label={null} // no label needed in the header
                        value={range}
                        onChange={setRange}
                        placeholder="Filter"
                        options={[
                            { label: "End of Month", value: "month" },
                            { label: "Next 7 Days", value: "7" },
                            { label: "Next 30 Days", value: "30" },
                        ]}
                    />
                </div>

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