import BreakdownChart from "./BreakdownChart";

export default function IncomeBreakdown({ categories }) {
    return (
        <BreakdownChart
            title="Income Breakdown"
            titleColor="text-green-700"
            categories={categories}
        />
    );
}