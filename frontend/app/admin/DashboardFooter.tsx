export default function DashboardFooter() {
    return (
        <footer className="w-full border-t border-gray-200 bg-white px-6 py-4">
            <div className="flex items-center justify-between text-sm text-gray-400">
                <p>©Modern Electronics Ltd {new Date().getFullYear()}, All Rights Reserved. <b>Created By Kenpro Media</b></p>
                <span>V1.6.6</span>
            </div>
        </footer>
    );
}
