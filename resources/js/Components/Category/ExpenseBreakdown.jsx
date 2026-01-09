import BreakdownChart from "./BreakdownChart";

export default function ExpenseBreakdown({ categories }) {
    return (
        <BreakdownChart
            title="Expense Breakdown"
            titleColor="text-red-700"
            categories={categories}
        />
    );
}