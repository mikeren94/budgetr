import CreateCategory from '@/Components/Category/CreateCategory';
import MonthlySummary from '@/Components/Summary/MonthlySummary';
import CreateTransaction from '@/Components/Transaction/CreateTransaction';
import TransactionList from '@/Components/Transaction/TransactionList';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';

export default function Dashboard() {

    const [refreshFlag, setRefreshFlag] = useState(0);

    const triggerRefresh = () => setRefreshFlag(f => f + 1);

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
                    <CreateTransaction onSuccess={triggerRefresh} />
                    <MonthlySummary />
                </div>
            </div>
            
        </AuthenticatedLayout>
    );
}
