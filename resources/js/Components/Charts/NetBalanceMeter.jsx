const NetBalanceMeter = ({income, expenses, netSpend}) => {
    return (
        <div className="mt-4">
            <div className="flex justify-between text-sm text-gray-600 mb-1">
                <span>Used</span>
                <span>Remaining</span>
            </div>

            <div className="w-full h-4 bg-gray-200 rounded-full overflow-hidden relative">
                {/* Expenses portion */}
                <div
                    className={`h-full ${expenses <= income ? "bg-red-400" : "bg-red-600"}`}
                    style={{
                        width: `${Math.min((expenses / income) * 100, 100)}%`,
                    }}
                ></div>

                {/* Overspend overflow */}
                {expenses > income && (
                    <div
                        className="h-full bg-red-800 absolute top-0"
                        style={{
                            left: "100%",
                            width: `${((expenses - income) / income) * 100}%`,
                        }}
                    ></div>
                )}
            </div>

            <div className="flex justify-between text-sm mt-1">
                <span className="text-red-600">£{expenses} spent</span>
                <span className={netSpend >= 0 ? "text-green-600" : "text-red-600"}>
                    {netSpend >= 0 ? `£${netSpend} left` : `£${Math.abs(netSpend)} overspent`}
                </span>
            </div>
        </div>
    )
}

export default NetBalanceMeter;