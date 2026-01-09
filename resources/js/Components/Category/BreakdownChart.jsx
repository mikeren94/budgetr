import { Doughnut } from "react-chartjs-2";

export default function BreakdownChart({ title, titleColor, categories }) {
    if (!categories || categories.length <= 0) return null;

    const data = {
        labels: categories.map(i => i.name),
        datasets: [
            {
                data: categories.map(i => i.total),
                backgroundColor: categories.map(i => i.color),
                borderWidth: 0,
            }
        ]
    };

    return (
        <>
            <h2 className={`text-xl font-semibold mb-4 ${titleColor}`}>
                {title}
            </h2>
            <Doughnut data={data} />
        </>
    );
}