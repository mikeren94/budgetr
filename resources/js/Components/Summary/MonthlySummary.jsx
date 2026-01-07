import { useEffect, useState } from "react";
import NetBalanceMeter from "../Charts/NetBalanceMeter";

const MonthlySummary = () => {
    const [income, setIncome] = useState(0);
    const [expenses, setExpenses] = useState(0);
    const [month, setMonth] = useState('');
    const netSpend = income - expenses;

    useEffect(() => {
        setIncome(2000);
        setExpenses(200);
        setMonth("January 2026");
    }, []);

    return (
        <div className="bg-white rounded-lg shadow p-4 max-w-md w-full">
            <h2 className="text-xl font-semibold tracking-tight text-gray-900 mb-4">
                Monthly Summary
            </h2>

            <div className="space-y-2 mb-4">
                <div className="flex items-baseline gap-1">
                    <span className="text-gray-500 tracking-wide">Month:</span>
                    <span className="font-semibold text-lg">{month}</span>
                </div>

                <div className="flex items-baseline gap-1">
                    <span className="text-gray-500 tracking-wide">Income:</span>
                    <span className="text-green-600 font-semibold text-lg">£{income}</span>
                </div>

                <div className="flex items-baseline gap-1">
                    <span className="text-gray-500 tracking-wide">Expenses:</span>
                    <span className="text-red-600 font-semibold text-lg">£{expenses}</span>
                </div>

                <div className="flex items-baseline gap-1">
                    <span className="text-gray-500 tracking-wide">Net spend:</span>
                    <span
                        className={`font-semibold text-lg ${
                            netSpend >= 0 ? "text-green-600" : "text-red-600"
                        }`}
                    >
                        £{netSpend}
                    </span>
                </div>
            </div>

            <NetBalanceMeter 
                income={income}
                expenses={expenses}
                netSpend={netSpend}
            />
        </div>
    );
};

export default MonthlySummary;