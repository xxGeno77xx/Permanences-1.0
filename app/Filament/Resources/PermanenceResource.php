<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Service;
use Filament\Forms\Form;
use App\Models\Permanence;
use Filament\Tables\Table;
use App\Models\Departement;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PermanenceResource\Pages;
use Awcodes\Curator\PathGenerators\DatePathGenerator;
use App\Filament\Resources\PermanenceResource\RelationManagers;
use App\Filament\Resources\PermanenceResource\Widgets\PermanenceList;

class PermanenceResource extends Resource
{
    protected static ?string $model = Permanence::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {   
        $serviceConnecte = Service::where('id', auth()->user()->service_id)
                                                ->value('departement_id');
        return $form
            ->schema([

                Repeater::make('users')
                    ->schema([
                        Select::make('participants')
                        ->label('Agents')
                        ->options(
                            User::whereHas('service', function ($query) use ($serviceConnecte) {
                                $query->where('departement_id', $serviceConnecte);
                            })->pluck('name','users.id')
                        )
                        ->multiple()
                        ->required()
                        ->columnSpan(1)
                    ])
                    ->addable(false),
                DatePicker::make('date_debut'),
                DatePicker::make('date_fin'),
                Hidden::make('departement_id')
                    ->default(Departement::whereHas('services', function ($query) use ($serviceConnecte) {
                        $query->where('departement_id', $serviceConnecte);
                    })->value('id'))
                // select::make('departement_id')
                //     ->label('Département')
                //     ->searchable()
                //     ->options(function (){
                //         return Departement::pluck('nom_departement','id');
                //     }),

                     
                        
                
                // CuratorPicker::make( 'file')
                // ->label('pdf correspondant')
                // ->buttonLabel('string | Htmlable | Closure $buttonLabel')
                // ->color('primary|secondary|success|danger') // defaults to primary
                // ->outlined(true) // defaults to true
                // ->size('sm') // defaults to md
                // ->constrained(true) // defaults to false (forces image to fit inside the preview area)
                // ->pathGenerator(DatePathGenerator::class) // see path generators below
                // ->lazyLoad() // defaults to true
                // ->listDisplay() // defaults to true
                // // see https://filamentphp.com/docs/2.x/forms/fields#file-upload for more information about the following methods
                // ->preserveFilenames()
                // // ->maxWidth()
                // // ->minSize()
                // // ->maxSize()
                // // ->rules()
                // // ->acceptedFileTypes()
                // // ->disk()
                // // ->visibility()
                // // ->directory()
                // // ->imageCropAspectRatio()
                // // ->imageResizeTargetWidth()
                // // ->imageResizeTargetHeight()
                // ->multiple() // required if using a relationship with multiple media
                
                
               
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_debut')
                ->label('Année')
                ->date('Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     // Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermanences::route('/'),
            'create' => Pages\CreatePermanence::route('/create'),
            'edit' => Pages\EditPermanence::route('/{record}/edit'),
        ];
    }   
    
    public static function getWidgets(): array
    {
        return [
            PermanenceList::class,
        ];
    }
}
