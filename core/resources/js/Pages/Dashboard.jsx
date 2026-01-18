import CreateCategory from '@/Components/Category/CreateCategory';
import MonthlySummary from '@/Components/Summary/MonthlySummary';
import CreateTransaction from '@/Components/Transaction/CreateTransaction';
import TransactionList from '@/Components/Transaction/TransactionList';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useState, useEffect, useMemo } from 'react';
import { useMonthlySummary } from '@/hooks/useMonthlySummary';
import ExpenseBreakdown from '@/Components/Category/ExpenseBreakdown';
import IncomeBreakdown from '@/Components/Category/IncomeBreakdown';
import { useCategoryBreakdown } from '@/Hooks/useCategoryBreakdown';
import { useUnpaidTransactions } from '@/Hooks/useUnpaidTransactions';
import UnpaidTransactions from '@/Components/Transaction/UnpaidTransactions';
import UpcomingTransactions from '@/Components/Transaction/UpcomingTransactions';
import Calendar from '@/Components/Calendar';
import useUpcomingTransactions from '@/hooks/useUpcomingTransactions';
import axios from 'axios';
import { useMonthlyTransactions } from "@/hooks/useMonthlyTransactions";

export default function Dashboard() {

    const currentMonth = new Date().toISOString().slice(0, 7);
    const [selectedMonth, setSelectedMonth] = useState(currentMonth);

    // Load monthly summary for the selected month
    const { data, loading, error } = useMonthlySummary(selectedMonth);
    const { transactions: monthlyTransactions, loading: monthlyLoading } =
        useMonthlyTransactions(selectedMonth);
    // Load unpaid transactions
    const {
        transactions: unpaidTransactions,
        loading: unpaidTransactionsLoading,
        refresh: refreshUnpaid
    } = useUnpaidTransactions();

    // Refresh unpaid transactions whenever the month changes
    useEffect(() => {
        refreshUnpaid();
    }, [selectedMonth]);

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
        refreshUpcoming();
    };

    const selectMonth = (e) => {
        setSelectedMonth(e.target.value);
    };

    const calendarByDate = useMemo(() => {
        const map = {};

        monthlyTransactions.forEach(t => {
            if (!t.category) return;

            const date = t.formatted_date; // "YYYY-MM-DD"
            if (!map[date]) {
                map[date] = { bills: 0, income: 0 };
            }

            if (t.category.type === "expense") {
                map[date].bills += 1;
            }

            if (t.category.type === "income") {
                map[date].income += 1;
            }
        });

        return map;
    }, [monthlyTransactions]);

    // -----------------------------
    // UPCOMING TRANSACTIONS SECTION
    // -----------------------------

    const [range, setRange] = useState(null);

    // Memoize params to prevent infinite loops
    const upcomingParams = useMemo(() => {
        if (range?.value === "month") return {};
        if (range?.value) return { range: range.value };
        return {};
    }, [range]);

    const {
        transactions: upcoming,
        loading: upcomingLoading,
        refresh: refreshUpcoming
    } = useUpcomingTransactions(upcomingParams);

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Dashboard
                    </h2>

                    <input
                        type="month"
                        value={selectedMonth}
                        onChange={selectMonth}
                        className="border rounded px-2 py-1 mt-2"
                    />
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="pt-4">
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6 px-6">

                    {unpaidTransactions.length > 0 && (
                        <div className="col-span-1 sm:col-span-2 lg:col-span-6 bg-white rounded-lg shadow p-4">
                            <UnpaidTransactions
                                transactions={unpaidTransactions}
                                loading={unpaidTransactionsLoading}
                                onMarkPaid={markTransactionPaid}
                            />
                        </div>
                    )}

                    <div className="col-span-1 sm:col-span-1 lg:col-span-2 md:col-span-3 bg-white rounded-lg shadow p-4">
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
                        <div className="col-span-1 sm:col-span-1 md:col-span-3 lg:col-span-2 bg-white rounded-lg shadow p-4">
                            <ExpenseBreakdown categories={expenseCategories} />
                        </div>
                    )}

                    {incomeCategories.length > 1 && (
                        <div className="col-span-1 sm:col-span-2 lg:col-span-3 bg-white rounded-lg shadow p-4">
                            <IncomeBreakdown categories={incomeCategories} />
                        </div>
                    )}

                    <div className="col-span-1 sm:col-span-1 md:col-span-6 lg:col-span-2 bg-white rounded-lg shadow p-4">
                        <UpcomingTransactions
                            transactions={upcoming}
                            loading={upcomingLoading}
                            range={range}
                            setRange={setRange}
                        />
                    </div>

                    <div className="col-span-1 sm:col-span-2 md:col-span-6 lg:col-span-6 bg-white rounded-lg shadow p-4 h-full flex flex-col">
                        <Calendar month={selectedMonth} calendarByDate={calendarByDate} />
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    );
}