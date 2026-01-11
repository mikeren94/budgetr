import { useMemo } from "react";

export default function Calendar({ month, billsByDate }) {
    // month = "2026-01"

    const days = useMemo(() => {
        const [year, monthNum] = month.split("-").map(Number);
        const firstDay = new Date(year, monthNum - 1, 1);
        const lastDay = new Date(year, monthNum, 0);

        const startDayOfWeek = firstDay.getDay(); // 0 = Sunday
        const totalDays = lastDay.getDate();

        const calendar = [];

        // Fill leading blanks
        for (let i = 0; i < startDayOfWeek; i++) {
            calendar.push(null);
        }

        // Fill actual days
        for (let day = 1; day <= totalDays; day++) {
            const dateStr = `${year}-${String(monthNum).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
            calendar.push({
                day,
                dateStr,
                bills: billsByDate[dateStr] ?? 0,
            });
        }

        return calendar;
    }, [month, billsByDate]);

    return (
        <div className="bg-white">
            <h3 className="text-lg font-semibold mb-4">Bills Calendar</h3>

            <div className="grid grid-cols-7 text-center font-medium text-gray-600 mb-2">
                <div>Sun</div>
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
            </div>

            <div className="grid grid-cols-7 gap-2">
                {days.map((day, idx) => (
                    <div
                        key={idx}
                        className="h-20 border rounded p-1 flex flex-col items-center justify-between"
                    >
                        {day ? (
                            <>
                                <span className="text-sm text-gray-700">{day.day}</span>

                                {day.bills > 0 && (
                                    <div className="text-xs bg-red-100 text-red-700 rounded px-2 py-0.5">
                                        {day.bills} bill{day.bills > 1 ? "s" : ""}
                                    </div>
                                )}
                            </>
                        ) : (
                            <span></span>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}