<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductImage;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $superAdmin = new User();
        $superAdmin->setEmail('superadmin@viefit-dz.com');
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN']);
        $superAdmin->setPassword($this->passwordHasher->hashPassword($superAdmin, 'ViefitAdmin2026!'));
        $manager->persist($superAdmin);

        $clientAdmin = new User();
        $clientAdmin->setEmail('admin@viefit-dz.com');
        $clientAdmin->setRoles(['ROLE_ADMIN']);
        $clientAdmin->setPassword($this->passwordHasher->hashPassword($clientAdmin, 'ViefitAdmin2026!'));
        $manager->persist($clientAdmin);

        $categoryStations = new Category();
        $categoryStations->setName('Stations Multifonctions');
        $categoryStations->setSlug('stations-multifonctions');
        $categoryStations->setDescription('Stations d\'entraînement complètes réunissant plusieurs postes indépendants pour un entraînement global du corps.');
        $categoryStations->setIsActive(true);
        $manager->persist($categoryStations);

        $categoryMusculation = new Category();
        $categoryMusculation->setName('Musculation Guidée');
        $categoryMusculation->setSlug('musculation-guidee');
        $categoryMusculation->setDescription('Machines guidées pour un travail musculaire ciblé, sécurisé et adapté à tous les niveaux.');
        $categoryMusculation->setIsActive(true);
        $manager->persist($categoryMusculation);

        $titan = new Product();
        $titan->setName('VieFit TITAN Multistation');
        $titan->setSlug('viefit-titan-multistation');
        $titan->setCategory($categoryStations);
        $titan->setShortDescription('Une machine. Plusieurs possibilités. Une seule priorité : la performance.');
        $titan->setDescription(
            "La VieFit TITAN est une station de musculation multifonction professionnelle conçue pour offrir un entraînement complet du corps. Plus qu'une simple machine, c'est un écosystème d'entraînement complet qui réunit plusieurs postes indépendants dans un design moderne et robuste.\n\n".
            "Grâce à ses multiples postes et ses poulies réglables, elle permet de travailler efficacement tous les principaux groupes musculaires : dos, pectoraux, épaules, bras, jambes et abdominaux.\n\n".
            "Construite pour durer, sa structure en acier haute résistance assure une stabilité maximale et une durabilité à toute épreuve, même en utilisation intensive. Elle intègre des systèmes de poulies de haute qualité avec roulements pour une fluidité exceptionnelle, et des câbles gainés haute résistance."
        );
        $titan->setBrand('VieFit');
        $titan->setReference('VF-TITAN-8ST');
        $titan->setKeyFeatures(implode("\n", [
            'Station multifonction complète pour un entraînement global',
            'Plusieurs postes d\'entraînement indépendants',
            'Systèmes de poulies haute qualité et charges guidées',
            'Poignées et accessoires multiples inclus',
            'Réglages faciles et rapides',
            'Structure en acier haute résistance (3mm)',
            'Grande stabilité et sécurité',
            'Design moderne et professionnel',
        ]));
        $titan->setTechnicalSpecs(implode("\n", [
            'Dimensions (L x l x H): 360 x 110 x 220 cm',
            'Poids net: 540 kg',
            'Structure: Acier haute résistance',
            'Charge des piles: 2 x 100 kg (réglables)',
            'Charge maximale utilisateur: 180 kg',
            'Nombre de postes: Multiple (jusqu\'à 8 exercices)',
            'Systèmes de poulies: Haute qualité avec roulements',
            'Câbles: Acier gainé haute résistance',
            'Accessoires inclus: Multiples poignées, barres, sangles de cheville',
        ]));
        $titan->setMainImage('image2-6a9d82b860631.jpg');
        $titan->setIsFeatured(true);
        $titan->setIsActive(true);
        $manager->persist($titan);

        foreach (['image3-6a9d82b860d0a.jpg', 'image5-6a9d82b861290.jpg', 'image6-6a9d82b8617ae.jpg', 'image7-6a9d82b861d28.jpg'] as $position => $filename) {
            $image = new ProductImage();
            $image->setFilename($filename);
            $image->setPosition($position);
            $image->setProduct($titan);
            $manager->persist($image);
        }

        $gripmaster = new Product();
        $gripmaster->setName('VieFit GRIPMASTER Multistation');
        $gripmaster->setSlug('viefit-gripmaster-multistation');
        $gripmaster->setCategory($categoryMusculation);
        $gripmaster->setShortDescription('For the ultimate grip & forearm power.');
        $gripmaster->setDescription(
            "La station multi-fonctions GRIPMASTER offre une solution complète pour développer la force de préhension, l'endurance et la taille des avant-bras. Avec ses postes multiples, elle permet une variété illimitée d'exercices de flexion, d'extension, de rotation et de pincement."
        );
        $gripmaster->setBrand('VieFit');
        $gripmaster->setReference('VF-GRIP-3ST');
        $gripmaster->setKeyFeatures(implode("\n", [
            'Conception modulaire 3 postes',
            'Construction robuste en acier',
            'Piles de charges individuelles (3 x 60 kg)',
            'Multiples points d\'attache pour accessoires',
            'Stockage de poids intégré (inclus 2.5kg, 5kg, 20kg)',
        ]));
        $gripmaster->setTechnicalSpecs(implode("\n", [
            'Dimensions (L x l x H): 180 x 150 x 210 cm',
            'Poids total: 320 kg',
            'Structure: Acier de 3mm',
            'Finition: Peinture poudre noire mate',
            'Charge de poids: 3 x 60 kg (standard)',
            'Garantie: 5 ans cadre, 1 an pièces',
        ]));
        $gripmaster->setMainImage('image9-6a9d82ba5a16e.jpg');
        $gripmaster->setIsFeatured(true);
        $gripmaster->setIsActive(true);
        $manager->persist($gripmaster);

        $hercules = new Product();
        $hercules->setName('VieFit HERCULES Dips Assist Machine');
        $hercules->setSlug('viefit-hercules-dips-assist');
        $hercules->setCategory($categoryMusculation);
        $hercules->setShortDescription('La force au service de vos performances.');
        $hercules->setDescription(
            "La VieFit Hercules est une machine de dips assistés haut de gamme qui permet de cibler intensément les triceps, les pectoraux inférieurs et les deltoïdes antérieurs. Grâce à son système d'assistance par contrepoids, elle permet d'adapter la charge selon le niveau et les objectifs de chaque utilisateur.\n\n".
            "Son design ergonomique assure un mouvement naturel et fluide, tout en garantissant une position stable et confortable. Robuste, fiable et conçue pour durer, la Hercules s'intègre parfaitement dans toutes les salles de sport professionnelles ou espaces d'entraînement privés."
        );
        $hercules->setBrand('VieFit');
        $hercules->setReference('VF-HERC-DA1');
        $hercules->setKeyFeatures(implode("\n", [
            'Travail ciblé : triceps, pectoraux inférieurs et épaules',
            'Système d\'assistance par contrepoids réglable',
            'Poignées multiples pour différentes prises',
            'Mouvement naturel et sécurisé',
            'Structure en acier haute résistance',
            'Rembourrages haute densité pour un confort optimal',
            'Finition peinture électrostatique anti-rayures et anticorrosion',
        ]));
        $hercules->setTechnicalSpecs(implode("\n", [
            'Dimensions (L x l x H): 145 x 124 x 115 cm',
            'Poids net: 130 kg',
            'Charge du contrepoids: Assisté / Réglable',
            'Structure: Acier haute résistance',
            'Capacité utilisateur: 150 kg max',
        ]));
        $hercules->setMainImage('image1-6a9d82bc35c7e.jpg');
        $hercules->setIsFeatured(false);
        $hercules->setIsActive(true);
        $manager->persist($hercules);

        $services = [
            ['Conseil et accompagnement', 'bi-chat-square-text', 'Nos experts vous conseillent dans le choix des équipements adaptés à votre espace, votre budget et vos objectifs.'],
            ['Livraison', 'bi-truck', 'Livraison de vos équipements sur tout le territoire national, avec un suivi de commande jusqu\'à réception.'],
            ['Installation', 'bi-tools', 'Installation professionnelle de vos machines par notre équipe technique qualifiée, dans le respect des normes de sécurité.'],
            ['Maintenance', 'bi-wrench-adjustable', 'Contrats de maintenance préventive pour garantir la longévité et la fiabilité de vos équipements.'],
            ['Réparation', 'bi-gear', 'Intervention rapide en cas de panne, avec des pièces de rechange adaptées à chaque machine.'],
            ['Assistance technique', 'bi-headset', 'Une équipe disponible pour répondre à vos questions techniques et vous accompagner au quotidien.'],
        ];

        foreach ($services as $position => [$title, $icon, $description]) {
            $service = new Service();
            $service->setTitle($title);
            $service->setIcon($icon);
            $service->setDescription($description);
            $service->setIsActive(true);
            $service->setPosition($position);
            $manager->persist($service);
        }

        $manager->flush();
    }
}
