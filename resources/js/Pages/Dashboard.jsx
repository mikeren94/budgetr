import CreateCategory from '@/Components/Category/CreateCategory';
import MonthlySummary from '@/Components/Summary/MonthlySummary';
import CreateTransaction from '@/Components/Transaction/CreateTransaction';
import TransactionList from '@/Components/Transaction/TransactionList';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { useMonthlySummary } from '@/hooks/useMonthlySummary'; // assuming this is where it lives
import ExpenseBreakdown from '@/Components/Category/ExpenseBreakdown';
import IncomeBreakdown from '@/Components/Category/IncomeBreakdown';
import { useCategoryBreakdown } from '@/Hooks/useCategoryBreakdown';
import { useUnpaidTransactions } from '@/Hooks/useUnpaidTransactions';
import UnpaidTransactions from '@/Components/Transaction/UnpaidTransactions';
import UpcomingTransactions from '@/Components/Transaction/UpcomingTransactions';
export default function Dashboard() {

    const [refreshFlag, setRefreshFlag] = useState(0);
    const triggerRefresh = () => setRefreshFlag(f => f + 1);
    const currentMonth = new Date().toISOString().slice(0, 7);
    const { data, loading, error } = useMonthlySummary(currentMonth);
    const {
        transactions: unpaidTransactions,
        loading: unpaidTransactionsLoading,
        refresh: refreshUnpaid
    } = useUnpaidTransactions();

    const transactions = data?.transactions ?? [];
    const income = data?.income ?? 0;
    const expenses = data?.expenses ?? 0;
    const net = data?.net ?? 0;
    const month = data?.month ?? "";

    const { income: incomeCategories, expenses: expenseCategories } =
        useCategoryBreakdown(transactions);

        
    const markTransactionPaid = async (id) => {
        const confirmed = window.confirm("Mark this transaction as paid?");
        if (!confirmed) return;

        await axios.put(`/api/transactions/${id}/mark-paid`);
        refreshUnpaid();
    }
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
            <div className="grid grid-cols-1 md:grid-cols-6 gap-6 px-6">
                { unpaidTransactions.length > 0 && (
                    <div className="col-span-6 bg-white rounded-lg shadow p-4">
                        <UnpaidTransactions transactions={unpaidTransactions} loading={unpaidTransactionsLoading} onMarkPaid={markTransactionPaid} />
                    </div>
                ) }

                <div className="col-span-2 bg-white rounded-lg shadow p-4">
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
                {expenseCategories.length > 0 && (
                    <div className="col-span-2 bg-white rounded-lg shadow p-4">
                        <ExpenseBreakdown categories={expenseCategories} />
                    </div>
                )}

                {incomeCategories.length > 1 && (
                    <div className="col-span-3 bg-white rounded-lg shadow p-4">
                        <IncomeBreakdown categories={incomeCategories} />
                    </div>
                )}

                <div className="col-span-2 bg-white rounded-lg shadow p-4">
                    <UpcomingTransactions />
                </div>

            </div>
        </div>
            
        </AuthenticatedLayout>
    );
}