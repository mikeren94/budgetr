import NetBalanceMeter from "../Charts/NetBalanceMeter";

const MonthlySummary = ({ income, expenses, net, month }) => {
    return (
        <>
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
                            net >= 0 ? "text-green-600" : "text-red-600"
                        }`}
                    >
                        £{net}
                    </span>
                </div>
            </div>

            <NetBalanceMeter 
                income={income}
                expenses={expenses}
                netSpend={net}
            />
        </>
    );
};

export default MonthlySummary;