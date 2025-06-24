const admin = document.getElementById("loginAdmin");
const member = document.getElementById("loginMember");

function toMember() {
    admin.classList.remove("translate-x-0");
    admin.classList.add("-translate-x-full");
    admin.classList.remove("z-20");
    admin.classList.add("z-10");

    member.classList.remove("translate-x-full");
    member.classList.add("translate-x-0");
    member.classList.remove("z-10");
    member.classList.add("z-20");
}

function toAdmin() {
    member.classList.remove("translate-x-0");
    member.classList.add("translate-x-full");
    member.classList.remove("z-20");
    member.classList.add("z-10");

    admin.classList.remove("-translate-x-full");
    admin.classList.add("translate-x-0");
    admin.classList.remove("z-10");
    admin.classList.add("z-20");
}
