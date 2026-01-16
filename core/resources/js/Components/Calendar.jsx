import { useMemo } from "react";

export default function Calendar({ month, calendarByDate }) {
    const days = useMemo(() => {
        const [year, monthNum] = month.split("-").map(Number);
        const firstDay = new Date(year, monthNum - 1, 1);
        const lastDay = new Date(year, monthNum, 0);

        const startDayOfWeek = firstDay.getDay();
        const totalDays = lastDay.getDate();

        const calendar = [];

        for (let i = 0; i < startDayOfWeek; i++) {
            calendar.push(null);
        }

        for (let day = 1; day <= totalDays; day++) {
            const dateStr = `${year}-${String(monthNum).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

            calendar.push({
                day,
                dateStr,
                bills: calendarByDate[dateStr]?.bills ?? 0,
                income: calendarByDate[dateStr]?.income ?? 0,
            });
        }

        return calendar;
    }, [month, calendarByDate]);

    return (
        <div className="bg-white h-full flex flex-col">
            <h3 className="text-lg font-semibold mb-4">Bills & Income Calendar</h3>

            {/* Weekday headers */}
            <div className="grid grid-cols-7 text-center font-medium text-gray-600 mb-2 sticky top-0 bg-white z-10">
                <div>Sun</div><div>Mon</div><div>Tue</div>
                <div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>

            {/* Calendar grid */}
            <div className="
                grid grid-cols-7 gap-2 flex-grow 
                auto-rows-fr
                sm:auto-rows-[80px]
                md:auto-rows-[100px]
                lg:auto-rows-fr
            ">
                {days.map((day, idx) => (
                    <div
                        key={idx}
                        className="
                            border rounded p-1 
                            flex flex-col items-center justify-between
                            bg-gray-50
                            aspect-square sm:aspect-auto
                        "
                    >
                        {day ? (
                            <>
                                <span className="text-sm text-gray-700">{day.day}</span>

                                <div className="flex flex-col gap-1 items-center">
                                    {day.bills > 0 && (
                                        <div className="text-xs bg-red-100 text-red-700 rounded px-2 py-0.5">
                                            {day.bills}
                                        </div>
                                    )}

                                    {day.income > 0 && (
                                        <div className="text-xs bg-green-100 text-green-700 rounded px-2 py-0.5">
                                            {day.income}
                                        </div>
                                    )}
                                </div>
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