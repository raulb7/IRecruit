<?php

namespace IRecruitBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Nelmio\Alice\Fixtures;

class LoadFixtures implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        Fixtures::load(
            __DIR__ . '/Fixtures.yml',
            $manager,
            [
                'providers' => [$this] // this option allows us to define your own fake functions inside this class
            ]
        );
    }

    public function Category()
    {
        $categories = [
            "Accounting",
            "General Business",
            "Admin & Clerical",
            "General Labor",
            "Pharmaceutical",
            "Automotive",
            "Government",
            "Professional Services",
            "Banking",
            "Grocery",
            "Purchasing Procurement",
            "Biotech",
            "Health Care",
            "Quality",
            "Broadcast Journalism",
            "Hotel Hospitality",
            "Real Estate",
            "Business Development",
            "Human Resources",
            "Research",
            "Construction",
            "Information Technology",
            "Food Service",
            "Consultant",
            "Retail",
            "Customer Service",
            "Insurance",
            "Sales",
            "Design",
            "Inventory",
            "Science",
            "Distribution",
            "Shipping",
            "Legal",
            "Skilled Labor Trades",
            "Education",
            "Planning",
            "Engineering",
            "Management",
            "Supply Chain",
            "Manufacturing",
            "Telecommunications",
            "Executive",
            "Marketing",
            "Training",
            "Facilities",
            "Journalism",
            "Transportation",
            "Finance",
            "Nonprofit Social Services",
            "Warehouse",
            "Franchise",
            "Nurse"
        ];

        return $categories[rand(0, count($categories) - 1)];
    }

    public function slugify($string)
    {
        return preg_replace(
            '/[^a-z0-9]/',
            '-',
            strtolower(trim(strip_tags($string)))
        );
    }

    public function CompanyName(){

        $name= [
            "Albertson's, Inc.",
            "Alcoa Inc.",
            "Alleghany Corporation",
            "Allegheny Energy, Inc.",
            "Allegheny Technologies Incorporated",
            "Allergan, Inc.",
            "ALLETE, Inc.",
            "Alliant Energy Corporation",
            "Allied Waste Industries, Inc.",
            "Allmerica Financial Corporation",
            "The Allstate Corporation",
            "ALLTEL Corporation",
            "The Alpine Group, Inc.",
            "Amazon.com, Inc.",
            "AMC Entertainment Inc.",
            "American Power Conversion Corporation",
            "Amerada Hess Corporation",
            "Raymond James Financial Inc.",
            "Reader's Digest Association Inc.",
            "Reebok International Ltd.",
            "Regions Financial Corp.",
            "Regis Corporation",
            "Reliance Steel & Aluminum Co.",
            "Reliant Energy Inc.",
            "Rent A Center Inc",
            "Republic Services Inc",
            "Revlon Inc",
            "RGS Energy Group Inc",
            "Rite Aid Corp",
            "Riverwood Holding Inc.",
            "RoadwayCorp",
            "Robert Half International Inc.",
            "Rock-Tenn Co",
            "Rockwell Automation Inc",
            "Rockwell Collins Inc",
            "Rohm & Haas Co.",
            "Ross Stores Inc",
            "RPM Inc.",
            "Ruddick Corp",
            "Ryder System Inc",
            "Ryerson Tull Inc",
            "Ryland Group Inc.",
            "Sabre Holdings Corp",
            "Safeco Corp",
            "Safeguard Scientifics Inc.",
            "Safeway Inc",
            "Saks Inc",
            "Sanmina-SCI Inc",
            "Sara Lee Corp",
            "SBC Communications Inc",
            "Scana Corp.",
            "Schering-Plough Corp",
            "Scholastic Corp",
            "SCI Systems Onc.",
            "Science Applications Intl. Inc.",
            "Scientific-Atlanta Inc",
            "Scotts Company",
            "Seaboard Corp",
        ];
        return $name[rand(0, count($name) - 1)];

    }
    public function userRoles($current)
    {
        if ($current <= 50){
            return ["ROLE_USER", "ROLE_COMPANY"];
        } else {
            return ["ROLE_USER"];
        }
    }

    public function jobType()
    {
        $chance = rand(1,3); if ($chance == 1) return "full-time"; else if ($chance == 2) return "part-time"; else return "freelance";
    }
    public function concat()
    {
        $result = '';

        foreach (func_get_args() as $string) {
            $result .= $string;
        }

        return $result;
    }
}