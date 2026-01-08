import CreateCategory from '@/Components/Category/CreateCategory';
import MonthlySummary from '@/Components/Summary/MonthlySummary';
import CreateTransaction from '@/Components/Transaction/CreateTransaction';
import TransactionList from '@/Components/Transaction/TransactionList';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { useMonthlySummary } from '@/hooks/useMonthlySummary'; // assuming this is where it lives

export default function Dashboard() {

    const [refreshFlag, setRefreshFlag] = useState(0);
    const triggerRefresh = () => setRefreshFlag(f => f + 1);

    const currentMonth = new Date().toISOString().slice(0, 7);
    const { data, loading, error } = useMonthlySummary(currentMonth);

    const transactions = data?.transactions ?? [];
    const income = data?.income ?? 0;
    const expenses = data?.expenses ?? 0;
    const net = data?.net ?? 0;
    const month = data?.month ?? "";
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">

                    {/* Pass transactions into your summary component */}
                    <MonthlySummary
                        income={income}
                        expenses={expenses}
                        net={net}
                        month={month}
                        transactions={transactions}
                        loading={loading}
                        error={error}
                    />
                </div>
            </div>
            
        </AuthenticatedLayout>
    );
}